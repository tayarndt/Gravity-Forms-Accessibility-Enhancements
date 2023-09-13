document.addEventListener("DOMContentLoaded", function() {
    // Move field up
    document.getElementById('reorderFieldsList').addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('move-up')) {
            event.preventDefault();
            let li = getClosest(event.target, 'li');
            let prev = li.previousElementSibling;

            if (prev) {
                li.parentNode.insertBefore(li, prev);
            }
        }
    });

    // Move field down
    document.getElementById('reorderFieldsList').addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('move-down')) {
            event.preventDefault();
            let li = getClosest(event.target, 'li');
            let next = li.nextElementSibling;

            if (next) {
                insertAfter(li, next);
            }
        }
    });

    // Fetch fields when form dropdown changes
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
});