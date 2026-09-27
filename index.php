<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Emergency Response Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            color: #222;
        }

        /* HEADER */
        .header {
            background: #b30000;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 26px;
            font-weight: bold;
        }

        /* MAIN */
        .container {
            width: 100%;
            margin: 0;
            position: relative;
        }

        #emergencyList {
            position: absolute;
            top: 20px;
            right: 20px;

            width: 350px;

            display: flex;
            flex-direction: column;
            gap: 15px;

              max-height: calc(100vh - 100px);
            overflow-y: auto;
        }

        /* EMERGENCY ITEM */
        .emergency-item {
            margin-bottom: 20px;
        }

        /* TIME ABOVE BOX */
        .emergency-time {
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
            margin-left: 5px;
        }

        /* EMERGENCY BOX */
        .emergency-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            cursor: pointer;
            border-left: 8px solid #777;
            box-shadow: 0 3px 10px rgba(0,0,0,0.10);
            transition: 0.2s;
        }

        .emergency-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        /* COLORS */
        .emergency-card.police {
            border-left-color: #1769aa;
        }

        .emergency-card.fire {
            border-left-color: #d32f2f;
        }

        .emergency-card.medical {
            border-left-color: #2e7d32;
        }

        /* TOP ROW */
        .emergency-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .emergency-type {
            font-size: 21px;
            font-weight: bold;
        }

        .barangay {
            font-size: 14px;
            color: #555;
            margin-top: 5px;
        }

        /* STATUS */
        .status {
            font-size: 12px;
            font-weight: bold;
            padding: 5px 9px;
            border-radius: 15px;
        }

        .status.unread {
            background: #ffe5e5;
            color: #c40000;
        }

        .status.read {
            background: #e4f6e8;
            color: #217a35;
        }

        /* LOCATION DETAILS */
        .emergency-details {
            display: none;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
        }

        .emergency-card.open .emergency-details {
            display: block;
        }

        .location {
            font-size: 12px;
            color: #555;
            margin-bottom: 10px;
        }

        /* MAP BUTTON */
        .map-button {
            display: inline-block;
            background: #1976d2;
            color: white;
            padding: 7px 11px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
        }

        .map-button:hover {
            background: #125ca5;
        }

        /* FUTURE FEATURES */
        .future-feature {
            margin-top: 12px;
            padding: 8px;
            background: #f5f5f5;
            border-radius: 6px;
            font-size: 11px;
            color: #888;
        }

        /* EMPTY */
        .empty {
            text-align: center;
            padding: 50px;
            color: #888;
        }

    </style>
</head>

<body>

<div class="header">
    Emergency Response Dashboard
</div>

<div class="container">

    <div id="emergencyList">
        <div class="empty">
            Loading emergencies...
        </div>
    </div>

</div>

<script>

let openEmergencyIDs = new Set();


/*
    LOAD EMERGENCIES
*/
function loadEmergencies() {

    /*
        IMPORTANT:
        Save which boxes are currently open
        before replacing the dashboard.
    */

    document.querySelectorAll(".emergency-card.open").forEach(card => {

        const id = card.dataset.id;

        if (id) {
            openEmergencyIDs.add(id);
        }

    });


    fetch("api/get_emergencies.php")

        .then(response => response.json())

        .then(data => {

            const list = document.getElementById("emergencyList");

            if (!data.success) {

                list.innerHTML = `
                    <div class="empty">
                        Failed to load emergencies.
                    </div>
                `;

                return;
            }


            if (!data.emergencies || data.emergencies.length === 0) {

                list.innerHTML = `
                    <div class="empty">
                        No emergencies reported.
                    </div>
                `;

                return;
            }


            let html = "";


            data.emergencies.forEach(emergency => {

                const id = String(emergency.id);

                const type = emergency.emergency_type;

                const barangay = emergency.barangay_id;

                const latitude = emergency.latitude;

                const longitude = emergency.longitude;

                const status = emergency.read_status || "UNREAD";

                const created = emergency.created_at;


                let cardClass = "";

                let icon = "";


                if (type === "POLICE") {

                    cardClass = "police";
                    icon = "🚔";

                }

                else if (type === "FIRE") {

                    cardClass = "fire";
                    icon = "🔥";

                }

                else if (type === "MEDICAL") {

                    cardClass = "medical";
                    icon = "🚑";

                }


                /*
                    Check if this emergency was already open.
                */

                const isOpen = openEmergencyIDs.has(id);

                const openClass = isOpen ? "open" : "";


                html += `

                    <div class="emergency-item">

                        <!-- TIME IS OUTSIDE THE BOX -->
                        <div class="emergency-time">
                            🕒 ${created}
                        </div>


                        <!-- CLICKABLE EMERGENCY BOX -->
                        <div
                            class="emergency-card ${cardClass} ${openClass}"
                            data-id="${id}"
                            onclick="openEmergency(this)"
                        >

                            <div class="emergency-header">

                                <div>

                                    <div class="emergency-type">
                                        ${icon} ${type} EMERGENCY
                                    </div>

                                    <div class="barangay">
                                        ${barangay}
                                    </div>

                                </div>


                                <div
                                    class="status ${status.toLowerCase()}"
                                >
                                    ${status === "READ" ? "✓ READ" : "● UNREAD"}
                                </div>

                            </div>


                            <!-- LOCATION -->
                            <div class="emergency-details">

                                <div class="location">

                                    📍
                                    ${latitude},
                                    ${longitude}

                                </div>


                                <a
                                    class="map-button"
                                    href="https://www.google.com/maps?q=${latitude},${longitude}"
                                    target="_blank"
                                    onclick="event.stopPropagation();"
                                >
                                    Open Location
                                </a>


                                <div class="future-feature">

                                    Future emergency controls can appear here.

                                </div>

                            </div>

                        </div>

                    </div>

                `;

            });


            list.innerHTML = html;


            /*
                Remove IDs that no longer exist.
                This prevents the Set from growing forever.
            */

            const currentIDs = new Set(
                data.emergencies.map(e => String(e.id))
            );


            openEmergencyIDs.forEach(id => {

                if (!currentIDs.has(id)) {
                    openEmergencyIDs.delete(id);
                }

            });

        })

        .catch(error => {

            console.error("Error:", error);

        });

}


/*
    OPEN / CLOSE EMERGENCY
*/
function openEmergency(card) {

    const id = String(card.dataset.id);


    /*
        TOGGLE OPEN STATE
    */

    if (card.classList.contains("open")) {

        card.classList.remove("open");

        openEmergencyIDs.delete(id);

    }

    else {

        card.classList.add("open");

        openEmergencyIDs.add(id);


        /*
            Mark as READ
        */

        const status = card.querySelector(".status");

        if (status) {

            status.classList.remove("unread");

            status.classList.add("read");

            status.innerHTML = "✓ READ";

        }


        /*
            Save READ status in database
        */

        const formData = new FormData();

        formData.append("id", id);


        fetch("api/mark_read.php", {

            method: "POST",

            body: formData

        })

        .then(response => response.json())

        .then(data => {

            console.log("Mark read:", data);

        })

        .catch(error => {

            console.error("Mark read error:", error);

        });

    }

}


/*
    INITIAL LOAD
*/

loadEmergencies();


/*
    AUTOMATIC REFRESH
    Every 2 seconds.

    The openEmergencyIDs Set makes sure
    opened boxes STAY OPEN.
*/

setInterval(loadEmergencies, 2000);

</script>

</body>
</html>
