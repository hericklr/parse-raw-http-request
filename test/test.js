'use strict';

let

    init =
    () => {
        let
            inputs = document.querySelectorAll('input[type="tel');
        window.removeEventListener('load', init);
        document.getElementsByTagName('form')[0].addEventListener('reset', reset_form);
        document.getElementsByTagName('form')[0].addEventListener('submit', submit_form);
        for (let i = 0, t = inputs.length; i < t; ++i) {
            inputs[i].addEventListener('keypress', filter_input);
        }
    },

    reset_form =
    () => {
        document.querySelector('#response_container > pre').innerHTML = '{}';
    },

    submit_form =
    event => {
        event.preventDefault();
        document.querySelector('#response_container > pre').innerHTML = '{}';
        fetch(
                'test.php', {
                    method: 'PUT',
                    body: new FormData(event.target)
                }
            )
            .then(
                response => response.text()
            )
            .then(
                result => {
                    document.querySelector('#response_container > pre').innerHTML = result;
                }
            )
            .catch(
                error => {
                    document.querySelector('#response_container > pre').innerHTML = error;
                }
            );
    },

    filter_input =
    event => {
        let
            key = (event.which) ? event.which : event.keyCode;
        if (key < 48 || key > 57) {
            event.preventDefault();
        }
    };

window.addEventListener('load', init);