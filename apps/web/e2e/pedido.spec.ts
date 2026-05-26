import { test, expect } from '@playwright/test'

test('login → cria pedido → confirma → fatura → verifica NF e parcelas', async ({ page }) => {
  // 1. Login
  await page.goto('http://localhost:3000/login')
  await page.fill('input[type="email"]', 'faturador.b@erp.local')
  await page.fill('input[type="password"]', 'fatura123')
  await page.click('button[type="submit"]')

  // Aguarda redirecionar para /pedidos
  await page.waitForURL('**/pedidos')
  await expect(page.locator('h2')).toContainText('Pedidos')

  // 2. Cria novo pedido
  await page.click('text=Novo Pedido')
  await page.waitForURL('**/pedidos/novo')

  // Seleciona cliente
  await page.selectOption('select[name="cliente_id"]', { index: 1 })

  // Data de emissão
  await page.fill('input[name="data_emissao"]', '2026-01-15')

  // Frete
  await page.fill('input[name="frete"]', '10')

  // Parcelas
  await page.fill('input[name="parcelas"]', '2')

  // Seleciona produto no item
  await page.selectOption('select[name="itens.0.produto_id"]', { index: 1 })

  // Quantidade e preço
  await page.fill('input[name="itens.0.quantidade"]', '2')
  await page.fill('input[name="itens.0.preco_unitario"]', '100')

  // Cria pedido
  await page.click('text=Criar Pedido')
  await page.waitForURL('**/pedidos')

  // 3. Clica no pedido criado
  await page.click('tr:last-child')
  await page.waitForURL('**/pedidos/**')

  // 4. Confirma pedido
  await page.click('text=Confirmar')
  await page.waitForTimeout(1000)

  // 5. Fatura pedido
  await page.click('text=Faturar')
  await page.waitForTimeout(1500)

  // 6. Verifica NF gerada
  await expect(page.locator('text=Nota Fiscal')).toBeVisible()
  await expect(page.locator('text=rascunho')).toBeVisible()

  // 7. Verifica parcelas geradas
  await expect(page.locator('text=Contas a Receber')).toBeVisible()
  await expect(page.locator('text=Parcela 1/2')).toBeVisible()
  await expect(page.locator('text=Parcela 2/2')).toBeVisible()
})