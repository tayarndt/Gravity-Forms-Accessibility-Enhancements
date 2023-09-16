document.addEventListener("DOMContentLoaded", function() {

    let reorderFieldsList = document.getElementById('reorderFieldsList');
    let fieldOrderInput = document.getElementById('field_order_input');

    // Move field up
    reorderFieldsList.addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('move-up')) {
            event.preventDefault();
            let li = getClosest(event.target, 'li');
            let prev = li.previousElementSibling;

            if (prev) {
                li.parentNode.insertBefore(li, prev);
                updateFieldOrder();
            }
        }
    });

    // Move field down
    reorderFieldsList.addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('move-down')) {
            event.preventDefault();
            let li = getClosest(event.target, 'li');
            let next = li.nextElementSibling;

            if (next) {
                insertAfter(li, next);
                updateFieldOrder();
            }
        }
    });

    // Function to update the hidden input with the current order of fields
    function updateFieldOrder() {
        let order = Array.from(reorderFieldsList.children).map(li => li.getAttribute('data-id')).join(',');
        fieldOrderInput.value = order;
    }

    // If you want to fetch new fields when the dropdown changes (Not yet fully implemented):
    document.getElementById('gf_forms_dropdown').addEventListener('change', function(event) {
        let selectedFormId = event.target.value;
        fetchFormFields(selectedFormId);
    });

    // Fetch fields when form dropdown changes (Placeholder for future implementation)
    function fetchFormFields(formId) {
        // Use AJAX to get fields of the selected form and display them
        // This part needs server-side handling to return form fields based on the selected form
    }

    function getClosest(elem, selector) {
        for (; elem && elem !== document; elem = elem.parentNode) {
            if (elem.matches(selector)) return elem;
        }
        return null;
    }

    function insertAfter(newNode, referenceNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    }

    // Initialize field order on page load
    updateFieldOrder();

});
