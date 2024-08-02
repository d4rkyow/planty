// Initial price based on default selected option
var selectedValue = document.getElementById('planSelect');
var oldPriceLbl = document.getElementById('oldPrice');
var discountedPriceLbl = document.getElementById('discountedPrice');
var pricePerMonthLbl = document.getElementById('pricePerMonth');
var formProduct = document.getElementById('formProduct');
var giftCheckBox = document.getElementById('flexCheckDefault');

document.addEventListener('DOMContentLoaded', function () {
    temp = selectedValue.selectedIndex;

    var oldPrice = pricings[temp].price;
    var discountedPrice = Math.ceil(oldPrice -
        (oldPrice * pricings[temp].discount));

    oldPriceLbl.innerText = formatCurrency(oldPrice);
    discountedPriceLbl.innerText = formatCurrency(discountedPrice);
    pricePerMonthLbl.innerText = formatCurrency((discountedPrice / pricings[temp].months));

    var route = selectedValue.options[temp].value;
    formProduct.action = route;

});

// Update prices when user changes the selected option
selectedValue.addEventListener('change', function () {
    var temp = this.selectedIndex;

    temp = selectedValue.selectedIndex;

    var oldPrice = pricings[temp].price;
    var discountedPrice = Math.ceil(oldPrice -
        (oldPrice * pricings[temp].discount));

    oldPriceLbl.innerText = formatCurrency(oldPrice);
    discountedPriceLbl.innerText = formatCurrency(discountedPrice);
    pricePerMonthLbl.innerText = formatCurrency((discountedPrice / pricings[temp].months));

    var route = selectedValue.options[temp].value;
    formProduct.action = route;

    if (oldPrice == discountedPrice) {
        document.querySelector(".strikethrough-price").classList.add("disabled");
    } else {
        document.querySelector(".strikethrough-price").classList.remove("disabled");
    }
});

// Function to format currency in JavaScript
function formatCurrency(amount) {
    // Format number to have thousands separator (.) and decimal separator (,)
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount).replace('IDR', 'Rp');
}

// Function to parse currency in JavaScript
function parseCurrency(value) {
    return parseFloat(value.replace(/Rp|\./g, '').replace(',', '.'));
}

// function generateCode() {
// // Generate a random code (8 characters)
// return Math.random().toString(36).substr(2, 8).toUpperCase();
// }
