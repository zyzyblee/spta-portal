/**
 * SPTA Payment Monitoring System — script.js
 * Small, dependency-free enhancements. Every page still works if
 * JavaScript is disabled (all forms are plain HTML GET/POST).
 */

document.addEventListener('DOMContentLoaded', function () {
  initNavToggle();
  initDeleteConfirmations();
  initPaymentStatusSync();
});

/** Hamburger menu for the header nav on small screens. */
function initNavToggle() {
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('mainNav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    var isOpen = nav.classList.toggle('nav-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
}

/**
 * Any form with class="delete-form" and a data-confirm="..." message
 * will ask for confirmation before submitting. Used for deleting
 * strands, sections, and students.
 */
function initDeleteConfirmations() {
  var forms = document.querySelectorAll('.delete-form');
  forms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var msg = form.getAttribute('data-confirm') || 'Are you sure you want to delete this record? This cannot be undone.';
      if (!window.confirm(msg)) {
        e.preventDefault();
      }
    });
  });
}

/**
 * On the admin payment-editing screen, keep the date field in sync
 * with the status dropdown: disabled + cleared when "Unpaid", and
 * auto-filled with today's date the first time someone switches a
 * row to "Paid". The server still enforces the same rule either way.
 */
function initPaymentStatusSync() {
  var forms = document.querySelectorAll('.payment-update-form');
  forms.forEach(function (form) {
    var statusSelect = form.querySelector('.status-select');
    var dateInput = form.querySelector('input[type="date"]');
    if (!statusSelect || !dateInput) return;

    function syncDateState() {
      if (statusSelect.value === 'unpaid') {
        dateInput.value = '';
        dateInput.disabled = true;
      } else {
        dateInput.disabled = false;
        if (!dateInput.value) {
          dateInput.value = new Date().toISOString().split('T')[0];
        }
      }
    }

    statusSelect.addEventListener('change', syncDateState);
    syncDateState();
  });
}

/**
 * Populates the Section dropdown based on the chosen Strand, using
 * data already rendered into the page (no AJAX call needed). Used on
 * admin/students.php.
 *
 * @param {string} strandSelectId   id of the <select> for strand
 * @param {string} sectionSelectId  id of the <select> for section
 * @param {Object} sectionsByStrand { strandId: [{id, name}, ...] }
 * @param {?number} selectedStrandId   pre-selected strand (edit mode)
 * @param {?number} selectedSectionId  pre-selected section (edit mode)
 */
function initStrandSectionCascade(strandSelectId, sectionSelectId, sectionsByStrand, selectedStrandId, selectedSectionId) {
  var strandSelect = document.getElementById(strandSelectId);
  var sectionSelect = document.getElementById(sectionSelectId);
  if (!strandSelect || !sectionSelect) return;

  function populateSections(strandId, preselectSectionId) {
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    var list = sectionsByStrand[strandId] || [];
    list.forEach(function (sec) {
      var opt = document.createElement('option');
      opt.value = sec.id;
      opt.textContent = sec.name;
      if (preselectSectionId && String(preselectSectionId) === String(sec.id)) {
        opt.selected = true;
      }
      sectionSelect.appendChild(opt);
    });
  }

  strandSelect.addEventListener('change', function () {
    populateSections(this.value, null);
  });

  if (selectedStrandId) {
    populateSections(selectedStrandId, selectedSectionId);
  }
}
