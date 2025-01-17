<?php
/**
 * Barcode Buddy for Grocy
 *
 * PHP version 7
 *
 * LICENSE: This source file is subject to version 3.0 of the GNU General
 * Public License v3.0 that is attached to this project.
 *
 *  A screen to supervise barcode scanning.
 *
 * @author     Marc Ole Bulling
 * @copyright  2019 Marc Ole Bulling
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html  GNU GPL v3.0
 * @since      File available since Release 1.0
 */

require_once __DIR__ . "/incl/configProcessing.inc.php";
require_once __DIR__ . "/incl/config.inc.php";
require_once __DIR__ . "/incl/redis.inc.php";

$CONFIG->checkIfAuthenticated(true);


?>
<!DOCTYPE html>
<html>
<head>


    <link rel="apple-touch-icon" sizes="57x57" href="./incl/img/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="./incl/img/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="./incl/img/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="./incl/img/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="./incl/img/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./incl/img/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="./incl/img/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./incl/img/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="./incl/img/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="./incl/img/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./incl/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="./incl/img/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./incl/img/favicon/favicon-16x16.png">
    <meta name="msapplication-TileImage" content="./incl/img/favicon/ms-icon-144x144.png">
    <meta name="msapplication-navbutton-color" content="#ccc">
    <meta name="msapplication-TileColor" content="#ccc">
    <meta name="apple-mobile-web-app-status-bar-style" content="#ccc">
    <meta name="theme-color" content="#ccc">


    <title>Barcode Buddy Screen</title>
    <style>
        body,
        html {
            padding: 0;
            margin: 0;
            position: relative;
            height: 100%
        }


        .main-container {
            height: 100%;
            display: flex;
            display: -webkit-flex;
            flex-direction: column;
            -webkit-flex-direction: column;
            -webkit-align-content: stretch;
            align-content: stretch;
        }

        .header {
            width: 100%;
            background: #ada47a;
            padding: 10px;
            box-sizing: border-box;
            flex: 0 1 auto;
            position: relative;
            z-index: 10;
            box-shadow: 0 3px 5px rgba(57, 63, 72, 0.3);
        }

        .content {
            background: #eee;
            width: 100%;
            padding: 10px;
            flex: 1 0 auto;
            box-sizing: border-box;
            text-align: center;
            align-content: center
        }

        .hdr-left {
            text-align: center;
            padding-left: 10px;
        }

        .hdr-right {
            float: right;
            width: 30%;
            text-align: right;
            padding-right: 10px
        }

        #micbuttondiv {
            position: fixed;
            bottom: 10px;
            left: 10px;
        }

        #selectbuttondiv {
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
        }

        #soundbuttondiv {
            position: fixed;
            bottom: 10px;
            right: 10px;
        }

        @font-face {
            font-family: 'jost-bold';
            src: url("incl/fonts/jost_bold.ttf");
        }

        @font-face {
            font-family: 'jost-medium';
            src: url("incl/fonts/jost_medium.ttf");
        }

        @font-face {
            font-family: 'jost-book';
            src: url("incl/fonts/jost_book.ttf");
        }

        .h1 {
            font: bold 3em jost-bold;
            margin: auto;
            text-align: center;
        }

        .h2 {
            font: bold 2.2em jost-bold;
            margin: auto;
            padding: 10px;
            text-align: center;
        }

        .h3 {
            font: bold 1.8em jost-bold;
            margin: auto;
            padding: 10px;
            text-align: center;
        }

        .h4 {
            font: bold 1.5em jost-bold;
            margin: auto;
            padding: 6px;
        }

        .h5 {
            font: bold 0.8em jost-medium;
            margin: auto;
            text-align: center;
        }

        *:focus {
            outline: none;
        }

        .bottom-button {
            background-color: #497C8D;
            color: white;
            padding: 1em 2em;
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 6px rgba(57, 63, 72, 0.5);
        }

        .bottom-img {
            height: 3em;
            width: 3em;
        }

        @media only screen and (orientation: portrait)  not (display-mode: fullscreen) {
            .bottom-button {
                padding: 2em 4em;
            }

            .bottom-img {
                height: 3em;
                width: 3em;
            }
        }

        .overlay {
            height: 0;
            width: 100%;
            position: fixed;
            z-index: 15;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.9);
            overflow-x: hidden;
            transition: 0.3s;
        }

        .overlay-content {
            position: relative;
            top: 12%;
            width: 100%;
            text-align: center;
            margin-top: 30px;
        }

        .overlay a {
            padding: 4px;
            text-decoration: none;
            font: bold 1em jost-bold;
            font-size: 36px;
            color: #efefef;
            display: block;
            transition: 0.2s;
        }

        .overlay a:hover, .overlay a:focus {
            color: #ffffff;
        }

        .overlay .closebtn {
            position: absolute;
            top: 20px;
            right: 45px;
            font-size: 60px;
        }

        @media screen and (max-height: 450px) {
            .overlay a {
                font-size: 20px
            }

            .overlay .closebtn {
                font-size: 40px;
                top: 15px;
                right: 35px;
            }
        }
    </style>

</head>
<body>
<script src="./incl/js/nosleep.min.js"></script>
<script src="./incl/js/he.js"></script>

<div class="main-container">
    <div id="header" class="header">
    <span class="hdr-right h4">
      Status: <span id="grocy-sse">Verbinden...</span><br>
    </span>
        <span id="mode" class="h1 hdr-left"></span>
    </div>
    <div id="content" class="content">
        <p id="scan-result" class="h2">Wenn Sie das hier länger als ein paar Sekunden lesen, prüfen Sie bitte, ob der Websocket-Server richtig gestartet wurde und läuft.</p>
        <div id="log">
            <p id="event" class="h3"></p><br>
            <div id="previous-events">
                <p class="h4 p-t10"> Letzte Aktionen: </p>
                <span id="log-entries" class="h5"></span>
            </div>
        </div>
    </div>
</div>

<audio id="beep_success" src="incl/websocket/beep.ogg" type="audio/ogg" preload="auto"></audio>
<audio id="beep_nosuccess" src="incl/websocket/buzzer.ogg" type="audio/ogg" preload="auto"></audio>
<div id="soundbuttondiv">
    <button class="bottom-button" onclick="toggleSound()" id="soundbutton"><img class="bottom-img" id="muteimg"
                                                                                src="incl/img/mute.svg"
                                                                                alt="Sound an-/ausschalten">
    </button>
</div>
<div id="selectbuttondiv">
    <button class="bottom-button" onclick="openNav()" id="selectbutton"><img class="bottom-img" src="incl/img/list.svg"
                                                                             alt="Modus auswählen">
    </button>
</div>
<div id="micbuttondiv">
    <button class="bottom-button" onclick="toggleMicListening()" id="micbutton"><img class="bottom-img" id="micimg"
                                                                                    src="incl/img/mic.svg"
                                                                                    alt="Mikrofon an-/ausschalten">
    </button>
</div>


<div id="myNav" class="overlay">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="overlay-content">
        <a href="#" onclick="sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_P"] ?>')">Einkaufen</a>
        <a href="#" onclick="sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_C"] ?>')">Verbrauchen</a>
        <a href="#" onclick="sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_O"] ?>')">Öffnen</a>
        <a href="#" onclick="sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_GS"] ?>')">Bestand anzeigen</a>
        <a href="#" onclick="sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_AS"] ?>')">Zur Einkaufsliste hinzufügen</a>
        <a href="#" onclick="sendQuantity()">Menge festlegen</a>
        <a href="#" onclick="sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_CA"] ?>')">Alle verbrauchen</a>
        <a href="#" onclick="sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_CS"] ?>')">Verbrauchen (verdorben)</a>
    </div>
</div>

<div id="productChooser" class="overlay">
    <a href="javascript:void(0)" class="closebtn" onclick="closeProductChooser()">&times;</a>
    <div class="overlay-content" id="productContainer">
    </div>
</div>

<script>

    function openNav() {
        document.getElementById("myNav").style.height = "100%";
    }

    function closeNav() {
        document.getElementById("myNav").style.height = "0%";
    }

    function openProductChooser(productArray) {
        let productContainer = document.getElementById("productContainer");
        productContainer.textContent = '';
        for (let i = 0; i < productArray.length; i++) {
            let product = productArray[i];
            let item = document.createElement('a');
            item.setAttribute('href', "#");
            item.onclick = function() { sendProduct(product); }
            item.appendChild(document.createTextNode(product.name));
            productContainer.appendChild(item);
        }
        document.getElementById("productChooser").style.height = "100%";
    }

    function closeProductChooser() {
        document.getElementById("productChooser").style.height = "0%";
    }

    function sendBarcode(barcode) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", "./api/action/scan?add=" + barcode, true);
        xhttp.send();
        xhttp = new XMLHttpRequest();
        xhttp.open("POST", "http://localhost:1234/info_transaction_state_changed?barcode=" + barcode, true);
        xhttp.send();
        closeNav();
    }

    function sendQuantity() {
        var q = prompt('Menge eingeben', '1');
        sendBarcode('<?php echo BBConfig::getInstance()["BARCODE_Q"] ?>' + q);
    }

    function sendProduct(product) {
        var xhttp = new XMLHttpRequest();
        if (product.id !== -1) {
            xhttp.open("GET", "./api/action/product?id=" + product.id, true);
        } else if ("sent_product_name" in product) {
            xhttp.open("GET", "./api/action/product?id=-1&name=" + product.sent_product_name, true);
        } else {
            xhttp.open("GET", "./api/action/product?id=-1&name=" + product.name, true);
        }
        xhttp.send();
        closeProductChooser();
    }

    var noSleep = new NoSleep();
    var wakeLockEnabled = false;
    var isFirstStart = true;
    var micMuted = true;


    function goHome() {
        if (document.referrer === "") {
            window.location.href = './index.php'
        } else {
            window.close();
        }
    }

    function toggleSound() {
        if (document.getElementById('beep_success').muted) {
            document.getElementById('beep_success').muted = false;
            document.getElementById('beep_nosuccess').muted = false;
            document.getElementById("muteimg").src = "incl/img/mute.svg";
        } else {
            document.getElementById('beep_success').muted = true;
            document.getElementById('beep_nosuccess').muted = true;
            document.getElementById("muteimg").src = "incl/img/unmute.svg";
        }
    }

    function toggleMicListening() {
        if (micMuted) {
            document.getElementById("micimg").src = "incl/img/mic_off.svg";
            micMuted = false;
        } else {
            document.getElementById("micimg").src = "incl/img/mic.svg";
            micMuted = true;
        }
        let xhttp = new XMLHttpRequest();
        xhttp.open("POST", "http://localhost:1234/toggle_listening", true);
        xhttp.send();
    }

    function resetMicImg() {
        document.getElementById("micimg").src = "incl/img/mic.svg";
    }

    function syncCache() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", "./cron.php", true);
        xhttp.send();
    }

    if (typeof (EventSource) !== "undefined") {
        syncCache()
        var source = new EventSource("incl/sse/sse_data.php");

        var currentScanId = 0;
        var connectFailCounter = 0;

        source.addEventListener("error", function (event) {
            switch (event.target.readyState) {
                case EventSource.CONNECTING:
                    document.getElementById('grocy-sse').textContent = 'Neuverbinden...';
                    // console.log('Reconnecting...');
                    connectFailCounter++
                    if (connectFailCounter === 100) {
                        source.close();
                        document.getElementById('grocy-sse').textContent = 'Nicht verfügbar';
                        document.getElementById('scan-result').textContent = 'Verbindung mit Barcode Buddy fehlgeschlagen';
                    }
                    break;
                case EventSource.CLOSED:
                    console.log('Connection failed (CLOSED)');
                    break;
            }
        }, false);

        async function resetScan(scanId) {
            await sleep(3000);
            if (currentScanId === scanId) {
                document.getElementById('content').style.backgroundColor = '#eee';
                document.getElementById('scan-result').textContent = 'Warte auf Barcode...';
                document.getElementById('event').textContent = '';
            }
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function resultScan(color, message, text, sound) {
            document.getElementById('content').style.backgroundColor = color;
            document.getElementById('event').textContent = message;
            document.getElementById('scan-result').textContent = text;
            document.getElementById(sound).play();
            if (text != null) {
                let limit = 3;
                let logEntries = "";
                document.getElementById('log-entries').innerText.split('\n', limit).map(i => {
                    logEntries = logEntries + i + '\n';
                });
                document.getElementById('log-entries').innerText = '\n' + text + logEntries;
            }
            currentScanId++;
            resetScan(currentScanId);
        }

        source.onopen = function () {
            document.getElementById('grocy-sse').textContent = 'Verbunden';
            if (isFirstStart) {
                isFirstStart = false;
                document.getElementById('scan-result').textContent = 'Warte auf Barcode...';
                var http = new XMLHttpRequest();
                http.open("GET", "incl/sse/sse_data.php?getState");
                http.send();
            }
        };

        source.onmessage = function (event) {
            var resultJson = JSON.parse(event.data);
            var resultCode = resultJson.data.substring(0, 1);
            var resultText = resultJson.data.substring(1);
            switch (resultCode) {
                case '0':
                    resultScan("#789f8a", "", he.decode(resultText), "beep_success");
                    break;
                case '1':
                    resultScan("#60837a", "Barcode erfolgreich nachgeschaut", he.decode(resultText), "beep_success");
                    break;
                case '2':
                    resultScan("#deb853", "Unbekannter Barcode", resultText, "beep_nosuccess");
                    break;
                case '4':
                    document.getElementById('mode').textContent = resultText;
                    break;
                case '5':
                    openProductChooser(JSON.parse(resultText))
                    break;
                case '6':
                    resultScan("#deb853", "Keine ähnlichen Produkte gefunden", null, "beep_nosuccess");
                    break;
                case '7':
                    resetMicImg();
                    break;
                case 'E':
                    document.getElementById('content').style.backgroundColor = '#e06a4e';
                    document.getElementById('grocy-sse').textContent = 'Getrennt';
                    document.getElementById('scan-result').style.display = 'none'
                    document.getElementById('previous-events').style.display = 'none'
                    document.getElementById('event').setAttribute('style', 'white-space: pre;');
                    document.getElementById('event').textContent = "\r\n\r\n" + resultText;
                    break;
            }
        };
    } else {
        document.getElementById('content').style.backgroundColor = '#e06a4e';
        document.getElementById('grocy-sse').textContent = 'Getrennt';
        document.getElementById('event').textContent = 'Entschuldigung, Ihr Browser unterstützt keine Server-Sent Events';
    }
</script>

</body>
</html>
