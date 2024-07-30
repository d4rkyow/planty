document.getElementById('pay-button').addEventListener('click', function (e) {
    e.preventDefault();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: '/process-payment',
        data: JSON.stringify(gift),
        type: 'POST',
        success: function (response) {
            const transaction = JSON.parse(response);

            // SnapToken acquired from previous step
            window.snap.pay(transaction.snap_token, {
                onSuccess: function (result) {
                    console.log(transaction.token);
                    window.location.href = '/process-payment/success/' + transaction.token;
                },
                onPending: function (result) {
                    /* You may add your own js here, this is just example */
                    // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                },
                // Optional
                onError: function (result) { }
            });
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
});


document.addEventListener("DOMContentLoaded", function () {
    var shippingTab = document.getElementById('nav-shippingInfo-tab');

    if (gift["isGift"]) {
        shippingTab.disabled = true;
        shippingTab.style.pointerEvents = 'none';
        shippingTab.style.opacity = '0.5';
    } else {
        shippingTab.disabled = false;
        shippingTab.style.pointerEvents = 'auto';
        shippingTab.style.opacity = '1';
    }
});