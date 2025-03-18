<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Country Dropdown</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6">
            <label for="country" class="form-label">Country</label>
            <select class="form-select" name="country" id="country" required>
                <option value="">Select Country</option>
                <!-- Countries will be added here dynamically -->
            </select>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const countryDropdown = document.getElementById("country");

            // Fetch the list of countries from the API
            fetch("https://restcountries.com/v3.1/all")
                .then(response => response.json())
                .then(data => {
                    // Sort the countries alphabetically by name
                    data.sort((a, b) => a.name.common.localeCompare(b.name.common));

                    // Populate the dropdown
                    data.forEach(country => {
                        const option = document.createElement("option");
                        option.value = country.name.common;
                        option.textContent = country.name.common;

                        // Set a selected option (optional)
                        if (country.name.common === "America") {
                            option.selected = true;
                        }

                        countryDropdown.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error("Error fetching country list:", error);
                    alert("Failed to load country list. Please try again later.");
                });
        });
    </script>
</body>
</html>
