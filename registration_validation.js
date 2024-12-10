function validate(form) {
    let fail = "";
    fail += validateUsername($('user').value);
    fail += validateID($('id').value);
    fail += validateEmail($('email').value);
    fail += validatePassword($('passwd').value);
    if (fail == "") return true;
    else { alert(fail); return false; }
}

function $(id) {
    return document.getElementById(id);
}

function validateUsername(field){
    if (field == "") return "No Username was entered.\n"
    else if (/[^a-zA-Z0-9_-]/.test(field))
        return "Only a-z, A-Z, 0-9, - and _ allowed in Usernames.\n"
    return ""
}

function validateID(field){
    if (field == "") return "No ID was entered.\n"
    else if (field.length != 9) // no ID's shorter than 9
        return "ID's must be 9 characters.\n"
    else if (/[^0-9]/.test(field))
        return "0-9 allowed in ID's.\n"
    return ""
}

function validateEmail(field) {
    if (field == "") 
        return "No Email was entered.\n";
    else if (!field.endsWith(".edu") || !field.endsWith(".com") || !field.endsWith(".org") || !field.endsWith(".gov") || !field.includes("@")) // Check for the correct domain
        return "Email must be properly formatted.\n";
    else if (/[^a-zA-Z0-9._@-]/.test(field))
        return "Invalid characters in Email.\n";
     else if (field.length < 10) // ensure user can't submit "@sjsu.edu" by itself
        return "Email can't be empty.\n"
    return "";
}

function validatePassword(field) {
    if (field == "") 
        return "No Password was entered.\n"
    else if (field.length < 6) // no passwords shorter than 6
        return "Passwords must be at least 6 characters.\n"
    else if (!/[a-z]/.test(field) || ! /[A-Z]/.test(field) ||!/[0-9]/.test(field))
        return "Passwords require at least one lowercase and uppercase letter and at least one number.\n"
    return ""
}