// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

// cypress/support/commands.js (or directly in the spec if you prefer)
Cypress.Commands.add('scrollIntoViewInDialogAndClick', (selectorOrEl) => {
  const getTarget = () =>
    typeof selectorOrEl === 'string' ? cy.get(selectorOrEl) : cy.wrap(selectorOrEl)

  getTarget()
    .should('exist')
    .then($el => {
      // Find the closest scroll container (your q-card has class "scroll")
      const $scrollParent =
        $el.closest('.q-card.scroll')[0] ||
        $el.closest('.scroll')[0] ||
        $el.closest('.q-dialog__inner')[0] ||
        $el.parent()[0]

      // Scroll that container so the element is centered/visible
      cy.wrap($scrollParent).scrollTo('top', { ensureScrollable: false }) // optional reset
      cy.wrap($scrollParent).scrollTo(0, $el[0].offsetTop - 200, { ensureScrollable: false })
    })

  getTarget()
    .scrollIntoView({ block: 'center', inline: 'center' })
    .should('be.visible')
    .click()
})
function pickRandomIndexes(count, howMany) {
  const unique = new Set()
  const n = Math.min(howMany, count)
  while (unique.size < n) {
    unique.add(Math.floor(Math.random() * count))
  }
  return [...unique]
}

function clickRandomFromList(selector, howMany = 2) {
  cy.get(selector).its('length').then(len => {
    const indexes = pickRandomIndexes(len, howMany)

    indexes.forEach(i => {
      // re-query each time (important)
      cy.get(selector)
        .eq(i)
        .scrollIntoView({ block: 'center' })
        .click({ force: true })
    })
  })
}
