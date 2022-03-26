/**
 * Define callback function for "sq-button"
 * @param {*} event
 */

 var result = '';

 function onGetCardNonce(event) {
 
     // Don't submit the form until SqPaymentForm returns with a nonce
     event.preventDefault();
 
     // Request a nonce from the SqPaymentForm object
     paymentForm.requestCardNonce();
 }
 
 // Initializes the SqPaymentForm object by
 // initializing various configuration fields and providing implementation for callback functions.
 var paymentForm = new SqPaymentForm({
     // Initialize the payment form elements
     applicationId: applicationId,
     locationId: locationId,
     inputClass: 'sq-input',
 
     // Customize the CSS for SqPaymentForm iframe elements
     inputStyles: [{
         backgroundColor: 'transparent',
         color: '#333333',
         fontFamily: '"Helvetica Neue", "Helvetica", sans-serif',
         fontSize: '16px',
         fontWeight: '400',
         placeholderColor: '#8594A7',
         placeholderFontWeight: '400',
         padding: '16px',
         _webkitFontSmoothing: 'antialiased',
         _mozOsxFontSmoothing: 'grayscale'
     }],
 
     // Initialize Google Pay button ID
     googlePay: {
         elementId: 'sq-google-pay'
     },
 
     // Initialize Apple Pay placeholder ID
     applePay: {
         elementId: 'sq-apple-pay'
     },
 
     // Initialize Masterpass placeholder ID
     masterpass: {
         elementId: 'sq-masterpass'
     },
 
     // Initialize the credit card placeholders
     cardNumber: {
         elementId: 'sq-card-number',
         placeholder: '•••• •••• •••• ••••'
     },
     cvv: {
         elementId: 'sq-cvv',
         placeholder: 'CVV'
     },
     expirationDate: {
         elementId: 'sq-expiration-date',
         placeholder: 'MM/YY'
     },
     
     // postalCode: {
     //     elementId: 'sq-postal-code'
     // },
     
     postalCode: false,
 
     // SqPaymentForm callback functions
     callbacks: {
 
         /*
          * callback function: methodsSupported
          * Triggered when: the page is loaded.
          */
         methodsSupported: function(methods) {
 
         },
 
         /*
          * callback function: createPaymentRequest
          * Triggered when: a digital wallet payment button is clicked.
          */
         createPaymentRequest: function() {
 
             var paymentRequestJson = {
                 requestShippingAddress: false,
                 requestBillingInfo: true,
                 shippingContact: {
                     familyName: "CUSTOMER LAST NAME",
                     givenName: "CUSTOMER FIRST NAME",
                     email: "mycustomer@example.com",
                     country: "USA",
                     region: "CA",
                     city: "San Francisco",
                     addressLines: [
                         "1455 Market St #600"
                     ],
                     postalCode: "94103",
                     phone: "14255551212"
                 },
                 currencyCode: "JPY",
                 countryCode: "JP",
                 total: {
                     label: "MERCHANT NAME",
                     amount: "1.00",
                     pending: false
                 },
                 lineItems: [{
                     label: "Subtotal",
                     amount: "1.00",
                     pending: false
                 }]
             };
 
             return paymentRequestJson;
         },
 
         /*
          * callback function: validateShippingContact
          * Triggered when: a shipping address is selected/changed in a digital
          *                 wallet UI that supports address selection.
          */
         validateShippingContact: function(contact) {
 
             var validationErrorObj;
             /* ADD CODE TO SET validationErrorObj IF ERRORS ARE FOUND */
             return validationErrorObj;
         },
 
         /*
          * callback function: cardNonceResponseReceived
          * Triggered when: SqPaymentForm completes a card nonce request
          */
         cardNonceResponseReceived: function(errors, nonce, cardData, billingContact, shippingContact) {
             if (errors) {
                 var error_html = "";
                 for (var i = 0; i < errors.length; i++) {
                     error_html += "<li> " + errors[i].message + " </li>";
                 }
                 document.getElementById("error").innerHTML = error_html;
                 // document.getElementById('sq-creditcard').disabled = false;
 
                 return;
             } else {
                 document.getElementById("error").innerHTML = "";
             }
 
             // Assign the nonce value to the hidden form field
             document.getElementById('card-nonce').value = nonce;
             var total = document.getElementById('payment-total').value;
             var order_id = document.getElementById('order-id').value;
             
                   $.post('https://bon-deli.com/shopping/card', { nonce: nonce, total: total, orderId: order_id }, function(response){
                 result = response;
                         console.log(result);
             });
             
         },
 
         /*
          * callback function: unsupportedBrowserDetected
          * Triggered when: the page loads and an unsupported browser is detected
          */
         unsupportedBrowserDetected: function() {
             /* PROVIDE FEEDBACK TO SITE VISITORS */
         },
 
         /*
          * callback function: inputEventReceived
          * Triggered when: visitors interact with SqPaymentForm iframe elements.
          */
         inputEventReceived: function(inputEvent) {
             switch (inputEvent.eventType) {
                 case 'focusClassAdded':
                     /* HANDLE AS DESIRED */
                     break;
                 case 'focusClassRemoved':
                     /* HANDLE AS DESIRED */
                     break;
                 case 'errorClassAdded':
                     /* HANDLE AS DESIRED */
                     break;
                 case 'errorClassRemoved':
                     /* HANDLE AS DESIRED */
                     break;
                 case 'cardBrandChanged':
                     /* HANDLE AS DESIRED */
                     break;
                 case 'postalCodeChanged':
                     /* HANDLE AS DESIRED */
                     break;
             }
         },
 
         /*
          * callback function: paymentFormLoaded
          * Triggered when: SqPaymentForm is fully loaded
          */
         paymentFormLoaded: function() {
             /* HANDLE AS DESIRED */
         }
     }
 });