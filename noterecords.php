<?php
session_start();
include 'db.php';
include 'header.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['branch_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];
$branch_id = $_SESSION['branch_id'];
$currentdate = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Chivo+Mono|Fira+Sans">
    <link href="styles.css" rel="stylesheet">
    <title>Queueing System</title>
    <style>
        .br-pagebody {
            margin-top: 10px;
            margin-left: auto;
            margin-right: auto;
            max-width: 1500px;
        }

        .br-section-wrapper {
            background-color: #fff;
            padding: 20px;
            margin-left: 0px;
            margin-right: 0px;
            box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.21);
        }

        .notediv {
            height: 90vh;
            max-height: 90vh;
            overflow: auto;
        }

        .top {
            top: -25px;
        }

    </style>
    
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="br-pagebody">
            <div class="br-section-wrapper notediv">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-4">
                <div style="display: flex; align-items: center;">
                    <div>
                        <h5 class="font-weight-bold" style="margin-bottom: -0.5rem;">Notes</h5>
                    </div>
                </div>
                <div>
                    <input type="date" class="form-control form-control-sm" id="filterdate" name="filterdate" value="<?php echo $currentdate; ?>">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectalldate">
                        <label class="form-check-label small" for="selectalldate">
                            Select All Date
                        </label>
                    </div>               
                </div>
                </div>
                <table id="notes" class="table table-hover table-sm mt-3" style="width: 100%;">
                    <thead class="sticky-top top">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 5%;">Branch</th>
                            <th style="width: 10%;">Client Name</th>
                            <th style="width: 30%;">Remarks</th>
                            <th style="width: 28%;">Note</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 12%;">Date</th>
                        </tr>
                    </thead>
                    <tbody class="small" id="notetable">
                    </tbody>
                </table>
                <div id="loading" class="loader mx-auto">
                    <div class="bar1"></div>
                    <div class="bar2"></div>
                    <div class="bar3"></div>
                    <div class="bar4"></div>
                    <div class="bar5"></div>
                    <div class="bar6"></div>
                    <div class="bar7"></div>
                    <div class="bar8"></div>
                    <div class="bar9"></div>
                    <div class="bar10"></div>
                    <div class="bar11"></div>
                    <div class="bar12"></div>
                </div>            
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/js/font-awesome.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        $(document).on('ajaxStart', function () {
            $('#loading').show();
        }).on('ajaxStop', function () {
            $('#loading').hide();
        });

        $(document).ready(function() {
        $(document).on('contextmenu',function(e) {
            e.preventDefault();
        });

        $(document).on('contextmenu', '#notes tbody tr', function(e) {
            e.preventDefault();
            $('#actiondropdown').remove();

            var rowData = $(this).children('td').map(function() {
                return $(this).text();
            }).get();
            console.log(rowData);
            var id = rowData[0]; 
            var menu = $('<div class="dropdown-menu" id="actiondropdown" style="display:block; position:absolute; z-index:1000;">'
                        + '<a class="dropdown-item small" href="preview.php?id=' + id + '" id="preview"><i class="fa fa-eye text-info" aria-hidden="true"></i> Preview</a>'
                        + '</div>').appendTo('body');
            menu.css({top: e.pageY + 'px', left: e.pageX + 'px'});

            $(document).on('click', function() {
                menu.remove();
            });
        });

        function loadRecords(filterdate) {
            if (filterdate) {
                $.ajax({
                    url: 'loadnoterecords.php',
                    method: 'GET',
                    data: {filterdate: filterdate},
                    success: function(data) {
                        $('#notetable').html(data);
                    }
                });
            } else {
                $.ajax({
                    url: 'loadnoterecords.php',
                    method: 'GET',
                    success: function(data) {
                        $('#notetable').html(data);
                    }
                });
            }
        }
        
        $('#selectalldate').on('click', function() {
            if ($(this).is(':checked')) {
                $('#filterdate').prop('disabled', true);
                loadRecords();
            } else {
                $('#filterdate').prop('disabled', false);
                var currentDate = $('#filterdate').val();
                loadRecords(currentDate);
            }
        });
        
        $('#filterdate').on('change', function() {
            var filterdate = $(this).val();
            loadRecords(filterdate);
        });
        
        var currentDate = $('#filterdate').val();
        loadRecords(currentDate);
        
        setInterval(function() {
            var filterdate = $('#filterdate').val();
            if ($('#selectalldate').is(':checked')) {
                loadRecords();
            } else {
                loadRecords(filterdate);
            }
        }, 10000);
});

</script>
</body>
</html>
