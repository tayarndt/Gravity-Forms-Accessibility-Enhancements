document.addEventListener("DOMContentLoaded", function() {
    let reorderFieldsList = document.getElementById('reorderFieldsList');
    let fieldOrderInput = document.getElementById('field_order_input');
    let formsDropdown = document.getElementById('gf_forms_dropdown');

    // Function to update the hidden input with the current order of fields
    function updateFieldOrder() {
        let order = Array.from(reorderFieldsList.children).map(li => li.getAttribute('data-id')).join(',');
        fieldOrderInput.value = order;
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

    // Fetch fields when form dropdown changes
    function fetchFormFields(formId) {
        let request = new XMLHttpRequest();
        request.open('POST', gf_accessibility.ajax_url, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                let response = JSON.parse(request.responseText);
                if (response.success) {
                    reorderFieldsList.innerHTML = '';
                    response.data.forEach(field => {
                        let li = document.createElement('li');
                        li.setAttribute('data-id', field.id);
                        li.innerHTML = `${field.label} 
                            <button type='button' class='move-up'>Move Up</button> 
                            <button type='button' class='move-down'>Move Down</button>`;
                        reorderFieldsList.appendChild(li);
                    });
                    updateFieldOrder();
                } else {
                    console.error(response.data);
                }
            } else {
                console.error('Server error.');
            }
        };

        request.send('action=fetch_form_fields&form_id=' + formId);
    }

    // Event listeners
    formsDropdown.addEventListener('change', function(event) {
        let selectedFormId = event.target.value;
        fetchFormFields(selectedFormId);
    });

    reorderFieldsList.addEventListener('click', function(event) {
        let li = getClosest(event.target, 'li');
        if (event.target.classList.contains('move-up')) {
            let prev = li.previousElementSibling;
            if (prev) {
                li.parentNode.insertBefore(li, prev);
                updateFieldOrder();
            }
        } else if (event.target.classList.contains('move-down')) {
            let next = li.nextElementSibling;
            if (next) {
                insertAfter(li, next);
                updateFieldOrder();
            }
        }
    });

    // Initialize on page load
    updateFieldOrder();
});
