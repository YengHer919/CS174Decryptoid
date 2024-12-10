function validate(form) {
    let fail = "";
    fail += validateInput($('field').value, $('file').value);
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

function $(id){
    return document.getElementById(id);
}