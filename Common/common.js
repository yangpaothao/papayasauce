function checkPassword(obj){
    $('body').data('txtpassword', $(obj).val());
    var regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$/;
    if($(obj).val().length < 8 && !regex.test($(obj).val())){
        alert("Make sure your password meets the minimum requirements.");
        $(obj).select();
        return(false);
    }
    else{
        return(true);
    }
}
function validateLogin(obj){
    if($(obj).val().length < 3){
        alert('Login must be atleast 3 characters long.');
        $(obj).focus();
        return(false);
    }
    else{
        return(true);
    }
}
function checkConfirmpassword(obj){
    var regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$/;
    if($(obj).val().length < 8 && !regex.test($(obj).val())){
        alert("Make sure your confirm password meets the minimum requirements.");
        $(obj).select();
        return(false);
    }
    if($('body').data('txtpassword') != "" && $(obj).val() != ""){
        if($('body').data('txtpassword') != $(obj).val()){
            alert("Password does not match, please try again.");
            $(obj).select();
            return(false);
        }
    }
    else{
        return(true);
    }
}
function getJDate(obj, isAlert){
    $(obj).datepicker({
        dateFormat: "mm/dd/yy",
        changeMonth: true,
        changeYear: true,
        onClose: function(){
            checkDate(obj, isAlert);
        }
    }).datepicker("show"); 
}
function checkDate(obj, isAlert){
    regexdate = /(0\d{1}|1[0-2])\/([0-2]\d{1}|3[0-1])\/(19|20)\d{2}/;
    if(!regexdate.test($(obj).val())){
        if(isAlert == true && $(obj).val() != ""){
            alert('You have entered an invalide date.  Please check and try again.');
        }
        $(obj).val('');
        $(obj).focus();
        return(false);
    }
    else{
        return(true);
    }
}
function escapeThisemail(thisemail){
    //We will escape the @ sign by putting '\' in  front.
    tempemail = thisemail.replace(/([.\\@])/g, "\\$1");
    return(tempemail);
}
function unEscapethisemail(thisemail){
    //We will remove the '\' from the email before we evaluate so the email will not have the '\' so it will become a valid email.
    tempemail = thisemail.replace(/\\/g, '');
    return(tempemail);
}
function validateEmail(thisEmail){
    let regex1 = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z]{2,4})+$/;
            ///^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if(regex1.test(thisEmail)){
        return(true);
    }
    else{
        alert("Please enter a valid email.");
        return(false);
    }
}
function checkTime(obj){
    thistime = $(obj).val();
    if(thistime.indexOf(':') == -1)
    {
        temptime1 = thistime.substr(0,2);
        temptime2 = thistime.substr(2);
        thistime = temptime1+":"+temptime2;
    }
    var regexp = /^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/;
    if(regexp.test(thistime) == false){
        alert('Please enter a correct time in format of military time 00:00 or 0000 between 00:00 - 23:59.');
        $(obj).val($('body').data($(obj).prop('id')));
        $(obj).focus();
        return(false);
    } 
    else{
        $(obj).val(thistime);
    }
        
}
function saveThisdata(obj){
    $('body').data($(obj).prop('id'), $(obj).val());
}
function isNumbercheck(obj){
    if(!$.isNumeric($(obj).val())){
        alert('Please enter an interger value.');
        $(obj).val();
        return(false);
    }
    else{
        return(true);
    }
}
function isPhonenumber(obj){
    if(obj.length != 10){
        alert("Make sure you enter a valid phone#.");
        return(false);
    }
}
function chkSearchdates(obj){
    //the date must be id or name ad txt_date
    //the from date must be txt_fromdate
    //the to date must be txt_todate
    //alert($(obj).prop('id'));
    //If txt_date exist, the other from and to must be empty and the revere is true
    if($(obj).prop('id') == "txt_date"){
        if($("#txt_fromdate").length && $("#txt_todate").length){
            $("#txt_fromdate").val("");
            $("#txt_todate").val("");
        }
    }
    else if($(obj).prop('id') == "txt_fromdate" || $(obj).prop('id') == "txt_todate"){
        $("#txt_date").val("");
    }
    
}
function validateCC(){
    if($("#txt_cr_last4").val() === ""){
        alert("Credit card number can't be empty.");
        return(false);
    }
    if($("#txt_expiredate_mm").val() === ""){
        alert("Credit card Month can't be empty.");
        return(false);
    }
    if($("#txt_expiredate_mm").val().length < 2){
        alert("Credit card Month is not valid.");
        return(false);
    }
    if($("#txt_expiredate_yy").val() === ""){
        alert("Credit card Year can't be empty.");
        return(false);
    }
    if($("#txt_expiredate_yy").val().length < 2){
        alert("Credit card Year is not valid.");
        return(false);
    }
    if($("#txt_security").val() === ""){
        alert("Credit card Security code can't empty.");
        return(false);
    }
    if($("#txt_security").val().length < 2){
        alert("Credit card Security code is not valid.");
        return(false);
    }
}
function goTonext(obj){
    //Visa, Discover, Master has 16
    //American has 15
    thisrdo = "";
    $(".payment_rdo").each(function(){
        if($(this).is(":checked")){
            thisrdo = $(this).val();
        }
    });
    if($(obj).prop('id') == "txt_cr_last4"){
        if(thisrdo == "Visa" || thisrdo == "Discover" || thisrdo == "Master")
        {
            if($(obj).val().length == 16){
                $("#txt_expiredate_mm").focus();
            }
        }
        else if(thisrdo == "American Express"){
            if($(obj).val().length == 15){
                $("#txt_expiredate_mm").focus();
            }
        }                        
    }
    else if($(obj).prop('id') == "txt_expiredate_mm"){
        if(thisrdo == "Visa" || thisrdo == "Discover" || thisrdo == "Master")
        {
            if($(obj).val().length == 2){
                $("#txt_expiredate_yy").focus();
            }
        }
        else if(thisrdo == "American Express"){
            if($(obj).val().length == 15){
                $("#txt_expiredate_yy").focus();
            }
        }
    }
    else if($(obj).prop('id') == "txt_expiredate_yy"){
        if(thisrdo == "Visa" || thisrdo == "Discover" || thisrdo == "Master")
        {
            if($(obj).val().length == 2){
                $("#txt_security").focus();
            }
        }
        else if(thisrdo == "American Express"){
            if($(obj).val().length == 15){
                $("#txt_security").focus();
            }
        }
    }
    else if($(obj).prop('id') == "txt_security"){
        if($(obj).val().length == 3){
            $("#btn_payment").prop('disabled', false);
            $("#btn_payment").focus();
        }
    }
}
function saveMydata(obj){
    //saved the data before it changed just in case we want to autofill it back if user failed to enter appropriate data.
    //ex, profile.php
    $("body").data($(obj).prop('id')+"_data", $(obj).val());
    //alert($("body").data($(obj).prop('id')+"_data"));
}