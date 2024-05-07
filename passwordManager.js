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
        const passwordFields = document.getElementsByName("password");

        for (let i = 0; i < passwordFields.length; i++) {
            sha256(passwordFields[i].value).then(hashedPassword => {
                passwordFields[i].value = hashedPassword;
                event.target.submit();
            });
        }

    });
}