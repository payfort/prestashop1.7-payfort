function submitMerchantPage(url, paymentMethod) {
    var baseDir = $('#baseDir').val();
    payfortFortMerchantPage.loadMerchantPage(baseDir + 'index.php', paymentMethod);
}
function submitMerchantPageInstallments(url, paymentMethod) {
    var baseDir = $('#baseDir').val();
    payfortFortMerchantPage.loadMerchantPage(baseDir + 'index.php', paymentMethod);
}

function showMerchantPage2Form() {
    $('#payfortfort_form').toggle();
//    $.uniform.update("select.form-control");
    
}