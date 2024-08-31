<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan Tiket Bus</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin: 15px 0 5px;
            color: #555;
        }
        input[type="text"], input[type="date"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .crud-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date();
            var minDate = today.toISOString().split('T')[0];

            var maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 45);
            var maxDateString = maxDate.toISOString().split('T')[0];

            document.getElementById("departure_date").setAttribute("min", minDate);
            document.getElementById("departure_date").setAttribute("max", maxDateString);

            document.getElementById("route").addEventListener("change", updateDestinations);
        });

        function updateDestinations() {
            var route = document.getElementById("route").value;
            var destination = document.getElementById("destination");
            var options = {
                ponorogo: ["Sragen", "Ngawi", "Madiun", "Ponorogo"],
                solo: ["Semarang", "Salatiga", "Boyolali", "Solo", "Matesih"],
                bojonegoro: ["Wirosari", "Blora", "Cepu", "Bojonegoro"],
                gemolong: ["Gubug", "Godong", "Purwodadi", "Sumberlawang", "Gemolong"]
            };

            destination.innerHTML = "";

            options[route].forEach(function(city) {
                var option = document.createElement("option");
                option.value = city;
                option.text = city;
                destination.appendChild(option);
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Form Pemesanan Tiket Bus</h2>
        <form action="process.php" method="POST">
            <label for="departure">Asal Keberangkatan:</label>
            <select id="departure" name="departure" required>
                <option value="Balaraja">Balaraja</option>
                <option value="BSD Serpong">BSD Serpong</option>
                <option value="Samsat BSD">Samsat BSD</option>
                <option value="Cilenggang">Cilenggang</option>
            </select>

            <label for="route">Rute:</label>
            <select id="route" name="route" required>
                <option value="ponorogo">Ponorogo</option>
                <option value="solo">Solo</option>
                <option value="bojonegoro">Bojonegoro</option>
                <option value="gemolong">Gemolong</option>
            </select>

            <label for="destination">Tujuan:</label>
            <select id="destination" name="destination" required>
                <!-- Options will be populated based on the selected route -->
            </select>

            <label for="departure_date">Tanggal Keberangkatan:</label>
            <input type="date" id="departure_date" name="departure_date" required>

            <label for="passenger_name">Nama Penumpang:</label>
            <input type="text" id="passenger_name" name="passenger_name" required>

            <label for="number_of_passengers">Jumlah Penumpang (max 10):</label>
            <input type="number" id="number_of_passengers" name="number_of_passengers" min="1" max="10" required>

            <input type="submit" value="Pesan Tiket">
        </form>
        <a class="crud-link" href="crud.php">Manage Orders</a>
    </div>
</body>
</html>