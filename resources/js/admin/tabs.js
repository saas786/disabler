/**
 * Scripting for WP-Admin Tabs.
 */

'use strict';

(function (window, document) {
  const tabWrapper = document.querySelector('.nav-tab-wrapper');
  const tabs = tabWrapper.querySelectorAll('.nav-tab');
  const panels = document.querySelectorAll('section[role="tabpanel"]');

  /**
   * Set the current tab by its ID.
   *
   * @param {string} tabId - The ID of the active tab.
   */
  function setActiveTab(tabId) {
    tabs.forEach((tab) => {
      const current = tab.id === tabId;
      tab.classList.toggle('nav-tab-active', current);
      tab.setAttribute('aria-selected', current);
    });

    panels.forEach((panel) => {
      if (panel.getAttribute('aria-labelledby') === tabId) {
        panel.removeAttribute('hidden');
        panel.classList.remove('hide-if-js');
      }
      else {
        panel.setAttribute('hidden', true);
      }
    });
  }

  /**
   * Get the ID of the active tab.
   *
   * @return {string} The ID of the active tab.
   */
  function getActiveTab() {
    const active = tabWrapper.querySelector('.nav-tab-active') || tabs[0];

    return active.id || '';
  }

  // Return early if there are no tabs on the page.
  if (!tabs || !panels) {
    return;
  }

  // Determine which tab should be selected.
  let currentTab = window.location.hash.substr(1);
  currentTab = currentTab ? `nav-tab-${currentTab}` : tabs[0].getAttribute('id');

  // Set the current tab and register the event listeners.
  setActiveTab(currentTab);
  tabWrapper.addEventListener('click', (e) => {
    if (e.target.tagName !== 'A') {
      return;
    }

    setActiveTab(e.target.id);
  });

  // Listen for other changes to the window hash.
  window.addEventListener('hashchange', () => {
    const id = window.location.hash.substr(1);

    if (id !== getActiveTab()) {
      setActiveTab(`nav-tab-${id}`);
    }
  });
}(window, document));
