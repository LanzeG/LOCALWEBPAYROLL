<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");



function getSchoolYears($conn) {
  $sql = "SELECT * FROM school_years";
  $result = mysqli_query($conn, $sql);
  $years = array();
  while ($row = mysqli_fetch_assoc($result)) {
      $years[] = $row['start_year'] . '-' . $row['end_year'];
  }
  return $years;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $startYear = $_POST['startYear'] ?? '';
  $endYear = $_POST['endYear'] ?? '';

  if (!empty($startYear) && !empty($endYear) && is_numeric($startYear) && is_numeric($endYear) && $endYear == $startYear + 1) {
      $conn = mysqli_connect($host, $user, $pass, $dbname);

      if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
      }

      $sql_check = "SELECT * FROM school_years WHERE start_year = ? AND end_year = ?";
      $stmt_check = mysqli_prepare($conn, $sql_check);
      mysqli_stmt_bind_param($stmt_check, "ii", $startYear, $endYear);
      mysqli_stmt_execute($stmt_check);
      mysqli_stmt_store_result($stmt_check);
      $num_rows = mysqli_stmt_num_rows($stmt_check);

      if ($num_rows > 0) {
          echo "Year already exists";
      } else {
          $sql_insert = "INSERT INTO school_years (start_year, end_year) VALUES (?, ?)";
          $stmt_insert = mysqli_prepare($conn, $sql_insert);
          mysqli_stmt_bind_param($stmt_insert, "ii", $startYear, $endYear);
          mysqli_stmt_execute($stmt_insert);
          echo "New school year added successfully";
      }

      mysqli_stmt_close($stmt_check);
      mysqli_close($conn);
  } else {
      echo "Invalid input. Please enter a valid school year range (e.g., 2023-2024)";
  }
  exit();
}

?>

            <!DOCTYPE html>
            <html lang="en">
                        
            <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <title>Manage Faculty Schedule</title>
              <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
              
              <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
              <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
              <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
              <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
              <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
              <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
                <!-- Bootstrap JavaScript -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
            <!--<div class="text-center pt-3">-->
            <!--  <h3>-->
            <!--SCHEDULE-->
            <!--</h3>-->
            <!--</div>-->
                   <style>
                    .day-label {
                      min-width: 100px;
                    }
                    body {
                      font-family: Poppins, sans-serif;
                    }
                    .dropdown {
                      position: relative;
                      display: inline-block;
                    }
                    .dropdown-content {
                      display: none;
                      position: absolute;
                      left: 50%;
                      transform: translateX(-33%);
                     
                      background-color: white;
                      min-width: 300px;
                      box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
                      z-index: 1;
                      padding: 20px;
                      border: 1px solid #ccc;
                      border-radius: 5px;
                    }
                    
                        .drop  {
                      display: none;
                      position: absolute;
                      left: 50%;
                      transform: translateX(0%) !important;
                
                      background-color: white;
                      min-width: 300px;
                      box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
                      z-index: 1;
                      padding: 20px;
                      border: 1px solid #ccc;
                      border-radius: 5px;
                    }
                    
                    .show {
                      display: block;
                    }
                  </style>
                    </head>

                        <body>
                            <?php
                        include('navbarAdmin.php');
                        ?>
                        <div class="container mx-auto py-5 flex justify-center items-center min-h-screen">
                            <div class="card shadow p-2">
                                <h2 class="text-center mb-4">
                                        Manage Faculty Schedule
                                </h2>
                                <div class="col-12 text-center mb-2">
                                <a href='currentsy.php' class='btn btn-success'>Current SY and Sem</a>
                                
                                </div>
                            <form action="schedule.php" method="POST" id="schedule-form" class="w-full max-w-3xl mx-auto">
                                <div class="top flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-1 mb-3 justify-center items-center flex-grow w-full">
                                    <select name="department" id="department-dropdown" class="border border-gray-300 p-2 rounded-md w-full sm:w-auto">
                                        <option value="">Select Department</option>
                                        <?php
                                       
                                        $sql = "SELECT dept_NAME FROM department";
                                        $result = $conn->query($sql);
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value='" . $row['dept_NAME'] . "'>" . $row['dept_NAME'] . "</option>";
                                            }
                                        }
                                        $conn->close();
                                        ?>
                                    </select>
                                    <select name="employee" id="employee-dropdown" class="border border-gray-300 p-2 rounded-md w-full sm:w-auto">
                                        <option value="">Select Name</option>
                                    </select>
                                    <div class="dropdown border border-gray-300 rounded-md w-full sm:w-auto " >
                                <button type="button" class=" apply-overload btn 0">School Year 
                                <i class="fa-solid fa-angle-down"></i></button>
                                <div class="dropdown-content  hidden mt-2 mx-auto drop">
                                    <div class="container mt-3">
                                        <div class="row">
                                            <div class="col-lg-12 p-0">
                                            <select name="year" id="year-picker" class="form-select">
                            <?php
                            // Connect to database
                            $conn = mysqli_connect($host, $user, $pass, $dbname);
                        
                            if (!$conn) {
                                die("Connection failed: " . mysqli_connect_error());
                            }
                        
                            // Get and display submitted years
                            $submittedYears = getSchoolYears($conn);
                            foreach ($submittedYears as $year) {
                                echo "<option>$year</option>";
                            }
                        
                            mysqli_close($conn);
                            ?>
                        </select>
                        </div>
                    </div>
                <div class="row mt-1 gap-0">
                    <div class="col-lg-12 p-0">
                        <div class="form-group">
                            <input type="number" id="start-year" name="startYear" class="form-control"
                                placeholder="Start Year (e.g., 2023)">
                        </div>
                    </div>
                    <div class="col-lg-12 p-0">
                        <div class="form-group">
                            <input type="number" id="end-year" name="endYear" class="form-control"
                                placeholder="End Year (e.g., 2024)">
                        </div>
                    </div>
                    <div class="form-group col-lg-12 p-0 text-center pt-1">
                        <button type="button" onclick="addSchoolYear()" class="btn btn-success">Add Year</button>
                    </div>
                </div>
            </div>
        </div>
        </dsiv>
                </div>
                
                
                
            <select name="semester" class="border border-gray-300 p-2 rounded-md w-full sm:w-auto">
              <option value="">Select Semester</option>
              <option value="1">1st Semester</option>
              <option value="2">2nd Semester</option>
              <option value="Mid">Mid year</option>
            </select>
        </div>
        <div id="subject-container" class="space-y-4">
        <!-- Monday -->
        <div class="subject-field">
          <div class="flex flex-col space-y-2 items-center sm:flex-row sm:space-y-0 sm:space-x-2">
            <span class="day-label">Monday:</span>
            <input type="text" name="monday_start" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Monday Start Time">
            <input type="text" name="monday_end" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Monday End Time">
            <div class="dropdown">
              <button type="button" class="apply-overload btn btn-primary text-white p-2 rounded-md">Overload <i class="fa-solid fa-caret-down"></i></button>
              <div class="dropdown-content monday-overload flex flex-col space-y-2 mt-2 mx-auto" style="max-width: 20px;">
                <button type="button" class="add-timepicker btn btn-success text-white p-1 rounded-md mt-2 w-full max-w-2xl mx-auto">+</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Tuesday -->
        <div class="subject-field">
          <div class="flex flex-col space-y-2 items-center sm:flex-row sm:space-y-0 sm:space-x-2">
            <span class="day-label">Tuesday:</span>
            <input type="text" name="tuesday_start" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Tuesday Start Time">
            <input type="text" name="tuesday_end" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Tuesday End Time">
            <div class="dropdown">
              <button type="button" class="apply-overload btn btn-primary text-white p-2 rounded-md">Overload <i class="fa-solid fa-caret-down"></i></button>
              <div class="dropdown-content tuesday-overload flex flex-col space-y-2 mt-2 mx-auto" style="max-width: 20px;">
                <button type="button" class="add-timepicker btn btn-success text-white p-1 rounded-md mt-2 w-full max-w-2xl mx-auto">+</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Wednesday -->
        <div class="subject-field">
          <div class="flex flex-col space-y-2 items-center sm:flex-row sm:space-y-0 sm:space-x-2">
            <span class="day-label">Wednesday:</span>
            <input type="text" name="wednesday_start" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Wednesday Start Time">
            <input type="text" name="wednesday_end" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Wednesday End Time">
            <div class="dropdown">
              <button type="button" class="apply-overload btn btn-primary text-white p-2 rounded-md">Overload <i class="fa-solid fa-caret-down"></i></button>
              <div class="dropdown-content wednesday-overload flex flex-col space-y-2 mt-2 mx-auto" style="max-width: 20px;">
                <button type="button" class="add-timepicker btn btn-success text-white p-1 rounded-md mt-2 w-full max-w-2xl mx-auto">+</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Thursday -->
        <div class="subject-field">
          <div class="flex flex-col space-y-2 items-center sm:flex-row sm:space-y-0 sm:space-x-2">
            <span class="day-label">Thursday:</span>
            <input type="text" name="thursday_start" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Thursday Start Time">
            <input type="text" name="thursday_end" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Thursday End Time">
            <div class="dropdown">
              <button type="button" class="apply-overload btn btn-primary text-white p-2 rounded-md">Overload <i class="fa-solid fa-caret-down"></i></button>
              <div class="dropdown-content thursday-overload flex flex-col space-y-2 mt-2">
                <button type="button" class="add-timepicker btn btn-success text-white p-1 rounded-md mt-2 w-full max-w-2xl mx-auto">+</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Friday -->
        <div class="subject-field">
          <div class="flex flex-col space-y-2 items-center sm:flex-row sm:space-y-0 sm:space-x-2">
            <span class="day-label">Friday:</span>
            <input type="text" name="friday_start" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Friday Start Time">
            <input type="text" name="friday_end" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Friday End Time">
            <div class="dropdown">
              <button type="button" class="apply-overload btn btn-primary text-white p-2 rounded-md">Overload <i class="fa-solid fa-caret-down"></i></button>
              <div class="dropdown-content friday-overload flex flex-col space-y-2 mt-2">
                <button type="button" class="add-timepicker btn btn-success text-white p-1 rounded-md mt-2 w-full max-w-2xl mx-auto">+</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Saturday -->
        <div class="subject-field">
          <div class="flex flex-col space-y-2 items-center sm:flex-row sm:space-y-0 sm:space-x-2">
            <span class="day-label">Saturday:</span>
            <input type="text" name="saturday_start" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Saturday Start Time">
            <input type="text" name="saturday_end" class="timepicker border border-gray-300 p-2 rounded-md flex-grow w-full sm:w-auto" placeholder="Saturday End Time">
              <div class="dropdown">
              <button type="button" class="apply-overload btn btn-primary text-white p-2 rounded-md">Overload <i class="fa-solid fa-caret-down"></i></button>
              <div class="dropdown-content saturday-overload flex flex-col space-y-2 mt-2">
                <button type="button" class="add-timepicker btn btn-success text-white p-1 rounded-md mt-2 w-full max-w-2xl mx-auto">+</button>
              </div>
            </div>
          </div>
        </div>
      <div class="mt-6 flex justify-center">
        <button type="submit" class="btn btn-success text-white p-2 rounded-md ">Apply</button>
      </div>
    </form>
  </div>
</div>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.timepicker').forEach(timepicker => {
      flatpickr(timepicker, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
      });
    });

    document.querySelectorAll('.apply-overload').forEach(button => {
      button.addEventListener('click', function () {
        this.nextElementSibling.classList.toggle('show');
      });
    });

    document.addEventListener('click', function (event) {
      if (!event.target.matches('.apply-overload')) {
        document.querySelectorAll('.dropdown-content').forEach(content => {
          content.classList.remove('show');
        });
      }
    });

    document.querySelectorAll('.dropdown-content').forEach(content => {
      content.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    });

    document.querySelectorAll('.add-timepicker').forEach(button => {
      button.addEventListener('click', function () {
        const overloadContainer = this.parentElement;
        const day = overloadContainer.classList[1].split('-')[0];

        if (overloadContainer.querySelectorAll('.flex.flex-row.items-center.space-x-2').length < 3) {
          const newOverload = document.createElement('div');
          newOverload.className = 'flex flex-col space-y-2';

          const uniqueId = Date.now();

        newOverload.innerHTML = `
              <div class="flex flex-row items-center justify-center space-x-2">
                <input type="text" name="${day}_overload_start[]" class="timepicker border border-gray-300 p-2  rounded-md w-full sm:w-1/3 lg:w-1/3" placeholder="${day.charAt(0).toUpperCase() + day.slice(1)} OL Start">
                <input type="text" name="${day}_overload_end[]" class="timepicker border border-gray-300 p-2  rounded-md w-full sm:w-1/3 lg:w-1/3" placeholder="${day.charAt(0).toUpperCase() + day.slice(1)} OL End">
                <div class="flex items-center space-x-1">
                  <label><input type="radio" name="${day}_overload_option_${uniqueId}" value="GD" required> GD</label>
                  <label><input type="radio" name="${day}_overload_option_${uniqueId}" value="UG" required> UG</label>
                </div>
                          <button type="button" class="delete-overload bg-red-500 text-white p-2 rounded-md"><i class="fa-solid fa-trash"></i></button>

              </div>
            `;

            overloadContainer.insertBefore(newOverload, this);
            flatpickr(newOverload.querySelectorAll('.timepicker'), {
              enableTime: true,
              noCalendar: true,
              dateFormat: "H:i",
              time_24hr: true
            });
            
               newOverload.querySelector('.delete-overload').addEventListener('click', function () {
        overloadContainer.removeChild(newOverload);
      });
          } else {
            Swal.fire({
            title: 'Maximum 3 Overload per day',
            icon: 'info',
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.onmouseenter = Swal.stopTimer;
              toast.onmouseleave = Swal.resumeTimer;
            }
          });
         
          }
        });
      });

    
    document.getElementById('department-dropdown').addEventListener('change', function() {
      var department = this.value;
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'get_employees.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        if (this.status === 200) {
          document.getElementById('employee-dropdown').innerHTML = this.responseText;
        }
      };
      xhr.send('department=' + department);
    });

function getQueryParams() {
  const params = new URLSearchParams(window.location.search);
  return {
    status: params.get('status'),
    message: params.get('message')
  };
}

const { status, message } = getQueryParams();
if (status && message) {
  Swal.fire({
    title: status === 'success' ? 'Success' : 'Error',
    text: decodeURIComponent(message),
    icon: status === 'success' ? 'success' : 'error',
    toast: true
  }).then(() => {
    window.history.replaceState({}, document.title, window.location.pathname);
    window.location.reload();
  });
}

  });
</script>


<script>
function addSchoolYear() {
    const startYear = document.getElementById('start-year').value.trim();
    const endYear = document.getElementById('end-year').value.trim();

    fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'startYear=' + startYear + '&endYear=' + endYear,
    })
    .then(response => response.text())
    .then(data => {
        console.log(data); // Log the response data
        if (data.includes('successfully')) {
            Swal.fire({
                title: 'Success',
                text: data,
                icon: 'success',
                toast: true,
                confirmButtonText: 'OK'
            }).then(() => {
                fetchSchoolYears();
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data,
                icon: 'error',
                toast: true,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function fetchSchoolYears() {
    fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>')
    .then(response => response.text())
    .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const optionsElement = temp.querySelector('#year-picker');
        if (optionsElement) {
            document.getElementById('year-picker').innerHTML = optionsElement.innerHTML;
            // Get the selected year value
            const selectedYear = document.getElementById('year-picker').value;
            console.log(selectedYear);

        } else {
            console.error('Year picker container not found in fetched HTML.');
        }
    })
    .catch(error => {
        console.error('Error fetching school years:', error);
    });
}


</script>
  
</body>

</html>
