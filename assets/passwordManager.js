function sha256(plainText) {
    const encoder = new TextEncoder();
    const data = encoder.encode(plainText);
    return crypto.subtle.digest("SHA-256", data).then(hash => {
        return Array.from(new Uint8Array(hash)).map(byte => {
            return byte.toString(16).padStart(2, '0');
        }).join('');
    });
}

function startLoginListener(formName) {
    const form = document.getElementById(formName);
    form.addEventListener("submit", function (event) {
        event.preventDefault();

        hashPassword(form);

        // Wait for all promises to resolve (hashing passwords)
        Promise.all(promises).then(() => {
            event.target.submit(); // Submit the form after hashing passwords
        });
    });
}

function hashPassword(form) {
    promises = []; // Initialize promises array

    const passwordFields = form.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        if (field.value.trim() !== '') {
            const promise = sha256(field.value).then(hashedPassword => {
                field.value = hashedPassword;
            });
            promises.push(promise); // Add promise to array
        }
    });
}

function startEventListener(formName) {
    const form = document.getElementById(formName);
    form.addEventListener("submit", function (event) {
        event.preventDefault();

        // Reset error messages
        document.getElementById('usernameError').textContent = "";
        document.getElementById('usernameError').classList.add('d-none');

        document.getElementById('emailError').textContent = "";
        document.getElementById('emailError').classList.add('d-none');

        document.getElementById('facultyNumberError').textContent = "";
        document.getElementById('facultyNumberError').classList.add('d-none');

        document.getElementById('registrationError').textContent = "";
        document.getElementById('registrationError').classList.add('d-none');


        hashPassword(form);

        // Wait for all promises to resolve (hashing passwords)
        Promise.all(promises).then(() => {
            fetch(form.action, {
                method: form.method,
                body: new FormData(form)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        if (data.field === 'username') {
                            displayError('usernameError', data.error);
                        } else if (data.field === 'email') {
                            displayError('emailError', data.error);
                        } else if (data.field === 'faculty_number') {
                            displayError('facultyNumberError', data.error);
                        } else {
                            displayError('registrationError', data.error);
                        }
                    } else {
                        // Check if password fields were hashed, assuming they are named 'password'
                        const passwordField = form.querySelector('input[name="password"]');
                        if (passwordField.value !== '' && passwordField.value.length !== 64) {
                            // If password was not hashed (length !== 64), hash it
                            sha256(passwordField.value).then(hashedPassword => {
                                passwordField.value = hashedPassword;
                                // Proceed to redirect to index.html
                                window.location.href = '../public/index.html';
                            });
                        } else {
                            window.location.href = '../public/login.html';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error during form submission:', error);
                });
        });
    });
}

