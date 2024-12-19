function validate(form) {
    let fail = "";
    fail += validateInput($('field').value, $('file').value);
    fail += validateKey($('key').value, $('cipher').value);
    if (fail === "") {
        return true;
    } else {
        alert(fail);
        return false;
    }
}

function validateInput(field, file) {
    if (field === "" && (!file || file === "")) {
        return "Must have either a text input or a file uploaded.\n";
    }
    return "";
}

function validateKey(key, cipher) {
    if (key == ""){
        return "Invalid key: must not be empty.";
    }
    // Check if key is a string
    if (typeof key !== "string") {
        return "Invalid key: must be a string.";
    }

    if(cipher == "simple_substitution"){
        // Check the length of the string
        if (key.length !== 26) {
            return "Invalid key: must be exactly 26 characters long.";
        }
    }

    // If all validations pass
    return "";
}

function $(id){
    return document.getElementById(id);
}