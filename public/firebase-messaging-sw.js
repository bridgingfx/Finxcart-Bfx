importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyAW5PFcpBPrk0585T_ylyvLZeN1bRfZzYU",
    authDomain: "finxcart-testing.firebaseapp.com",
    projectId: "finxcart-testing",
    storageBucket: "finxcart-testing.firebasestorage.app",
    messagingSenderId: "88760750172",
    appId: "1:88760750172:web:a020cf187283dfd74d9699",
    measurementId: "G-FS2V5PSHG3"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});