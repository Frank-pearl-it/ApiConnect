describe('template spec', () => {
  it('passes', () => {
    cy.visit('localhost')
  })
})
 
// its impossible to test registering a new user because cypress cant scan a qrcode for 2fa
describe('can perform the created features', () => {
  function pickRandomIndexes(count, howMany) {
    const unique = new Set()
    const n = Math.min(howMany, count)
    while (unique.size < n) unique.add(Math.floor(Math.random() * count))
    return [...unique]
  }

  // Click N random items matching selector, sequentially, always re-querying
  function clickRandom(selector, howMany = 2) {
    cy.get(selector).its('length').then(len => {
      const indexes = pickRandomIndexes(len, howMany)

      const clickNext = (pos = 0) => {
        if (pos >= indexes.length) return

        cy.get(selector)
          .eq(indexes[pos])
          .scrollIntoView({ block: 'center' })
          .click({ force: true })
          .then(() => {
            // give Quasar a tiny moment for expansion/transition DOM updates
            cy.wait(50)
          })
          .then(() => clickNext(pos + 1))
      }

      clickNext(0)
    })
  }

  // Click N random Quasar toggles/checkboxes that live inside a container selector
  // We explicitly click `.q-toggle__inner` / `.q-checkbox__inner` so it actually toggles.
  function clickRandomQuasarToggles(containerSelector, howMany = 2) {
    const toggleSelector = [
      '.q-toggle__inner',
      '.q-checkbox__inner',
      'input[type="checkbox"]'
    ].join(',')

    cy.get(containerSelector)
      .find(toggleSelector)
      .filter(':visible')
      .its('length')
      .then(len => {
        if (!len) return

        const indexes = pickRandomIndexes(len, howMany)

        const clickNext = (pos = 0) => {
          if (pos >= indexes.length) return

          cy.get(containerSelector)
            .find(toggleSelector)
            .filter(':visible')
            .eq(indexes[pos])
            .scrollIntoView({ block: 'center' })
            .click({ force: true })
            .then(() => cy.wait(50))
            .then(() => clickNext(pos + 1))
        }

        clickNext(0)
      })
  }

  it('should login and logout successfully', () => {
    cy.visit('localhost')

    cy.get('.twofa-card').click()
    cy.get('input[type="email"]').type('test@example.com')
    cy.get('input[type="password"]').type('password')
    cy.get('.login').click()

    cy.get('input[type="text"]').type('PjYRV4JkgH-YKAv3eGyCj')
    cy.get('.login').click()

    cy.contains('[role="listitem"]', 'gebruikers').click()

    cy.get('.roles-button').click()
    cy.contains('.q-btn', 'Nieuwe rol').click()
    cy.get('.rolename').type('Test rol')
    cy.get('.role-description').type('test rol voor automatisch testen')
    // ---- Role permissions section
    cy.get('.role-expand').click()
    cy.get('.role-header').first().should('exist').scrollIntoView({ block: 'center' })

    // Click 2 random role headers (expand/collapse groups)
    clickRandom('.role-header', 2)

    // Click 2 random actual toggles inside the role toggle grid/blocks
    clickRandomQuasarToggles('.role-toggle', 2)

    cy.get('.role-expand').click()

    // ---- Ticket access section
    cy.get('.ticket-expand').click()
    cy.get('.ticket-header').first().should('exist').scrollIntoView({ block: 'center' })

    // Click 2 random ticket headers
    clickRandom('.ticket-header', 2)

    // Click 2 random ticket toggles (actual toggle inners, not the wrapper div)
    clickRandomQuasarToggles('.ticket-toggle', 2)

    cy.get('.ticket-expand').click()

    cy.get('.save-role').click()
    cy.contains('Rol succesvol opgeslagen').should('be.visible')

    cy.get('.contact').click()
    cy.contains('Contact').should('be.visible')

    cy.contains('Email: helpdesk@pearl-it.nl').should('be.visible')
    cy.contains('Tel: +31 (0)13 - 203 20 78').should('be.visible')
    cy.get('.logout').click()
  })
}) 



