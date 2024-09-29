<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wikipedia-style RSA Messaging Applet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsencrypt/3.2.1/jsencrypt.min.js"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Lato', 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #202122;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #a2a9b1;
            border-radius: 2px;
            padding: 20px;
            margin-top: 20px;
        }
        h1, h2 {
            border-bottom: 1px solid #a2a9b1;
            padding-bottom: 5px;
        }
        .nav-tabs {
            border-bottom: 1px solid #a2a9b1;
        }
        .nav-tabs .nav-link.active {
            border-color: #a2a9b1 #a2a9b1 #fff;
        }
        .form-control, .btn {
            border-radius: 2px;
        }
        .btn-primary {
            background-color: #3366cc;
            border-color: #3366cc;
        }
        .btn-secondary {
            background-color: #a2a9b1;
            border-color: #a2a9b1;
            color: #000;
        }
        #userPage {
            border: 1px solid #a2a9b1;
            border-radius: 2px;
            padding: 20px;
            margin-top: 20px;
            background-color: #ffffff;
        }
        #publicMessages {
            list-style-type: none;
            padding-left: 0;
        }
        #publicMessages li {
            background-color: #eaecf0;
            border: 1px solid #a2a9b1;
            border-radius: 2px;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Wikipedia-style RSA Messaging Applet</h1>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="keys-tab" data-bs-toggle="tab" data-bs-target="#keys" type="button" role="tab" aria-controls="keys" aria-selected="true">Keys</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="encrypt-tab" data-bs-toggle="tab" data-bs-target="#encrypt" type="button" role="tab" aria-controls="encrypt" aria-selected="false">Encrypt</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="decrypt-tab" data-bs-toggle="tab" data-bs-target="#decrypt" type="button" role="tab" aria-controls="decrypt" aria-selected="false">Decrypt</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="keys" role="tabpanel" aria-labelledby="keys-tab">
                <h2 class="mt-3">Key Generation</h2>
                <button id="generateKeysBtn" class="btn btn-primary">Generate Key Pair</button>
                <button id="downloadKeysBtn" class="btn btn-secondary ms-2" style="display: none;">Download Key Pair</button>
                <div class="mt-3">
                    <label for="publicKeyDisplay" class="form-label">Public Key</label>
                    <textarea id="publicKeyDisplay" class="form-control" rows="3" readonly></textarea>
                </div>
                <div class="mt-3">
                    <label for="privateKeyDisplay" class="form-label">Private Key</label>
                    <textarea id="privateKeyDisplay" class="form-control" rows="3" readonly></textarea>
                </div>
            </div>
            <div class="tab-pane fade" id="encrypt" role="tabpanel" aria-labelledby="encrypt-tab">
                <h2 class="mt-3">Encryption</h2>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" class="form-control" rows="3" placeholder="Enter your message"></textarea>
                </div>
                <div class="mb-3">
                    <label for="recipientUsername" class="form-label">Recipient's Username</label>
                    <input type="text" id="recipientUsername" class="form-control" placeholder="Enter recipient's username">
                </div>
                <button id="encryptBtn" class="btn btn-primary">Encrypt and Send</button>
                <div class="mt-3">
                    <label for="encryptedMessage" class="form-label">Encrypted Message</label>
                    <textarea id="encryptedMessage" class="form-control" rows="3" readonly placeholder="Encrypted message will appear here"></textarea>
                </div>
            </div>
            <div class="tab-pane fade" id="decrypt" role="tabpanel" aria-labelledby="decrypt-tab">
                <h2 class="mt-3">Decryption</h2>
                <div class="mb-3">
                    <label for="privateKey" class="form-label">Your Private Key</label>
                    <textarea id="privateKey" class="form-control" rows="3" placeholder="Enter your private key"></textarea>
                </div>
                <div class="mb-3">
                    <label for="encryptedMessageToDecrypt" class="form-label">Encrypted Message</label>
                    <textarea id="encryptedMessageToDecrypt" class="form-control" rows="3" placeholder="Enter the encrypted message"></textarea>
                </div>
                <button id="decryptBtn" class="btn btn-primary">Decrypt</button>
                <div class="mt-3">
                    <label for="decryptedMessage" class="form-label">Decrypted Message</label>
                    <textarea id="decryptedMessage" class="form-control" rows="3" readonly placeholder="Decrypted message will appear here"></textarea>
                </div>
            </div>
        </div>

        <div id="userPage" style="display: none;">
            <h2 id="userPageTitle">User: <span id="username"></span></h2>
            <div id="publicKeySection">
                <h3>Public Key</h3>
                <textarea id="userPublicKey" class="form-control" rows="3" readonly></textarea>
            </div>
            <div id="messagesSection">
                <h3>Public Messages</h3>
                <ul id="publicMessages"></ul>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        const crypt = new JSEncrypt({default_key_size: 4096});
        const users = {};

        document.getElementById('generateKeysBtn').addEventListener('click', function() {
            crypt.getKey();
            document.getElementById('publicKeyDisplay').value = crypt.getPublicKey();
            document.getElementById('privateKeyDisplay').value = crypt.getPrivateKey();
            document.getElementById('downloadKeysBtn').style.display = 'inline-block';
        });

        document.getElementById('downloadKeysBtn').addEventListener('click', function() {
            const publicKey = document.getElementById('publicKeyDisplay').value;
            const privateKey = document.getElementById('privateKeyDisplay').value;
            const keyPair = `Public Key:\n${publicKey}\n\nPrivate Key:\n${privateKey}`;
            const blob = new Blob([keyPair], { type: 'text/plain' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'rsa_key_pair.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });

        document.getElementById('encryptBtn').addEventListener('click', function() {
            const message = document.getElementById('message').value;
            const recipientUsername = document.getElementById('recipientUsername').value;
            
            if (!users[recipientUsername]) {
                users[recipientUsername] = {
                    publicKey: crypt.getPublicKey(),
                    messages: []
                };
            }
            
            const encryptor = new JSEncrypt();
            encryptor.setPublicKey(users[recipientUsername].publicKey);
            const encryptedMessage = encryptor.encrypt(message);
            
            users[recipientUsername].messages.push(encryptedMessage);
            
            document.getElementById('encryptedMessage').value = encryptedMessage;
            updateUserPage(recipientUsername);
        });

        document.getElementById('decryptBtn').addEventListener('click', function() {
            const encryptedMessage = document.getElementById('encryptedMessageToDecrypt').value;
            const privateKey = document.getElementById('privateKey').value;
            
            const decryptor = new JSEncrypt();
            decryptor.setPrivateKey(privateKey);
            const decryptedMessage = decryptor.decrypt(encryptedMessage);
            
            document.getElementById('decryptedMessage').value = decryptedMessage;
        });

        function updateUserPage(username) {
            document.getElementById('userPage').style.display = 'block';
            document.getElementById('username').textContent = username;
            document.getElementById('userPublicKey').value = users[username].publicKey;
            
            const messagesList = document.getElementById('publicMessages');
            messagesList.innerHTML = '';
            users[username].messages.forEach((message, index) => {
                const li = document.createElement('li');
                li.textContent = `Message ${index + 1}: ${message}`;
                messagesList.appendChild(li);
            });
        }
    </script>
</body>
</html>