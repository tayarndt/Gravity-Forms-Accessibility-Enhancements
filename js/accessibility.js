document.addEventListener("DOMContentLoaded", function() {

    const formDropdown = document.getElementById('gf_forms_dropdown');
    const field1Dropdown = document.getElementById('gf_field_1_dropdown');
    const field2Dropdown = document.getElementById('gf_field_2_dropdown');
    const reorderFieldsList = document.getElementById('reorderFieldsList');
    const fieldOrderInput = document.getElementById('field_order_input');

    formDropdown.addEventListener('change', function() {
        fetchFormFields(this.value);
    });

    function fetchFormFields(formId) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', gf_accessibility.ajax_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                const resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    populateFieldsDropdown(resp.data);
                    updateOrderPreview(resp.data);
                } else {
                    alert('Error fetching fields: ' + resp.data);
                }
            } else {
                alert('Server error. Please try again.');
            }
        };
        xhr.send(encodeURI('action=fetch_form_fields&form_id=' + formId));
    }

    function populateFieldsDropdown(fields) {
        clearDropdown(field1Dropdown);
        clearDropdown(field2Dropdown);
        
        fields.forEach(function(field) {
            const optionLabel = `${field.label} (${field.type})`;
            const option1 = document.createElement("option");
            option1.value = field.id;
            option1.textContent = optionLabel;
            field1Dropdown.appendChild(option1);
            const option2 = option1.cloneNode(true);
            field2Dropdown.appendChild(option2);
        });
    }

    function clearDropdown(dropdown) {
        while (dropdown.firstChild) {
            dropdown.removeChild(dropdown.firstChild);
        }
    }

    function updateOrderPreview(fields) {
        while (reorderFieldsList.firstChild) {
            reorderFieldsList.removeChild(reorderFieldsList.firstChild);
        }

        fields.forEach(function(field) {
            const liLabel = `${field.label} (${field.type})`;
            const li = document.createElement("li");
            li.dataset.id = field.id;
            li.textContent = liLabel;
            reorderFieldsList.appendChild(li);
        });

        updateFieldOrderInput();
    }

    function moveFields(direction) {
        const field1 = document.getElementById('gf_field_1_dropdown').value;
        const field2 = document.getElementById('gf_field_2_dropdown').value;
        
        const field1Li = Array.from(reorderFieldsList.children).find(li => li.dataset.id === field1);
        const field2Li = Array.from(reorderFieldsList.children).find(li => li.dataset.id === field2);

        if (!field1Li || !field2Li) {
            return;
        }

        if (direction === 'above') {
            reorderFieldsList.insertBefore(field1Li, field2Li);
        } else if (direction === 'below') {
            if (field2Li.nextSibling) {
                reorderFieldsList.insertBefore(field1Li, field2Li.nextSibling);
            } else {
                reorderFieldsList.appendChild(field1Li);
            }
        }

        updateFieldOrderInput();
    }

    function updateFieldOrderInput() {
        const ids = Array.from(reorderFieldsList.children).map(li => li.dataset.id).join(',');
        fieldOrderInput.value = ids;
    }

    document.getElementById('move_above_btn').addEventListener('click', function() {
        moveFields('above');
    });

    document.getElementById('move_below_btn').addEventListener('click', function() {
        moveFields('below');
    });

    // Initial fetch to populate the fields on page load
    if (formDropdown.value) {
        fetchFormFields(formDropdown.value);
    }
});
