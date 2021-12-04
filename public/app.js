// Hide the flash message
if(typeof(document.getElementById('flash-error') ?? 'null') !== 'string') {

    document.querySelector('span#flash-close').addEventListener('click', (element) => {

        element.preventDefault();
        document.querySelector('div#flash-error').style.display = 'none';
        return false;

    });

}

// Display the query filter form
document.querySelector('a#display-form-control').addEventListener('click', (e) => {

    e.preventDefault();
    let filterForm = document.querySelector('div#filter-form');
    filterForm.setAttribute('class', 'row');
    window.setTimeout(() => {

        filterForm.children[2].style.transform = 'scaleY(1)';

    }, 30);
    return false;

});

// Hide the query filter form
document.querySelector('a#hide-form-control').addEventListener('click', (e) => {

    e.preventDefault();
    let filterForm = document.querySelector('div#filter-form');
    filterForm.children[2].style.transform = 'scaleY(0)';
    window.setTimeout(() => {

        filterForm.setAttribute('class', 'row no-filter');

    }, 250);
    return false;

});

// Reset the query filter form
document.querySelector('button#form-filter-reset').addEventListener('click', (e) => {

    document.location.href = e.target.getAttribute('data-redirect');
    return false;

});

// Reset the hostname filter
let hostnameResetButton = document.querySelector('div#hostname-field > i') ?? [];
if(hostnameResetButton.length !== 0) { // a little bit dirty...

    document.querySelector('div#hostname-field > i').addEventListener('click', (e) => {

        document.location.href = e.target.getAttribute('data-redirect');
        return false;

    });

}

// Changing page
let pageButtons = document.querySelectorAll('li.page-item:not(.disabled)');
if(pageButtons.length > 0) {

    pageButtons.forEach((element) => {

        element.addEventListener('click', (e) => {

            e.stopPropagation();
            e.preventDefault();

            document.getElementById('table_current_page').value = e.target.getAttribute('data-page') ?? e.target.parentNode.getAttribute('data-page');
            document.getElementById('table-form').childNodes[1].submit();
            return false;

        });

    });

}

// Set row count
document.querySelector('select#table_row_count').addEventListener('change', (e) => {

    document.getElementById('table-form').childNodes[1].submit();
    return false;

});

// Set the rows ordering
document.querySelectorAll('span.col-ordering').forEach(element => {

    element.addEventListener('click', (e) => {

        var target = e.target;
        if(target.tagName === 'SPAN') { // Click on the TH

            document.getElementById('table_current_order_col').value = e.target.getAttribute('data-ordering-col');
            document.getElementById('table_current_order_dir').value = e.target.getAttribute('data-ordering-dir');

        }
        else { // Click on the SPAN

            document.getElementById('table_current_order_col').value = e.target.parentNode.getAttribute('data-ordering-col');
            document.getElementById('table_current_order_dir').value = e.target.parentNode.getAttribute('data-ordering-dir');

        }

        document.getElementById('table-form').childNodes[1].submit();

        return false;

    });

});