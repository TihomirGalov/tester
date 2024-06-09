function sha256(plainText) {
    const encoder = new TextEncoder();
    const data = encoder.encode(plainText);
    return crypto.subtle.digest("SHA-256", data).then(hash => {
        return Array.from(new Uint8Array(hash)).map(byte => {
            return byte.toString(16).padStart(2, '0');
        }).join('');
    });
}

function startEventListener(formName) {
    document.getElementById(formName).addEventListener("submit", function (event) {
        event.preventDefault();
        const passwordFields = document.querySelectorAll('input[type="password"]');
        const promises = [];

        passwordFields.forEach(field => {
            if (field.value.trim() !== '') {
                const promise = sha256(field.value).then(hashedPassword => {
                    field.value = hashedPassword;
                });
                promises.push(promise);
            }
        });

        Promise.all(promises).then(() => {
            event.target.submit();
        });
    });
}
