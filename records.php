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
if ($branch_id == 8) {
    $querytotal = "SELECT SUM(cashonhand) as sumcashonhand FROM queueinfo WHERE cashonhandstatus = 'RECEIVED'";
} else {
    $querytotal = "SELECT SUM(cashonhand) as sumcashonhand FROM queueinfo WHERE cashonhandstatus = 'RECEIVED' AND branchid = '$branch_id'";
}

$resulttotal = mysqli_query($conn, $querytotal);
$rowtotal = mysqli_fetch_assoc($resulttotal);
$totaldemand = $rowtotal['sumcashonhand'];
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
            overflow: auto;
        }
        
        .br-section-wrapper {
            background-color: #fff;
            padding: 20px;
            margin-left: 0px;
            margin-right: 0px;
            box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.21);
        }

        .recordsdiv {
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
            <div class="br-section-wrapper recordsdiv">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-4">
                <div style="display: flex; align-items: center;">
                    <div>
                        <h5 class="font-weight-bold" style="margin-bottom: -0.5rem;">Records</h5>
                    </div>
                </div>
                <div>
                    <input type="date" class="form-control form-control-sm" id="filterdate" name="filterdate" value="<?php echo $currentdate; ?>" disabled>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectalldate" checked>
                        <label class="form-check-label small" for="selectalldate">
                            Select All Date
                        </label>
                    </div>               
                </div>
                </div>
                <table id="records" class="table table-hover table-sm mt-3" style="width: 100%;">
                    <thead class="sticky-top top">
                        <tr>
                            <th>Queue no.</th>
                            <th>Branch</th>
                            <th>Type</th>
                            <th>Client Name</th>
                            <th>Loan Amount</th>
                            <th>Total Balance</th>
                            <th>On-Hand Cash</th>
                            <th>COH Status</th>
                            <th>Active Number</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody class="small" id="recordtable">
                        <?php include 'loadrecords.php'; ?>
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
        <div class="text-center text-danger">
            <span class="font-weight-bold" id="totalonhandcash" style="font-size: 15px;"></span>
        </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/js/font-awesome.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        
        $(document).on('ajaxStart', function () {
            $('#totalonhandcash').text('Total Running Collection: ' + '₱' + totalOnHandCash());
            $('#loading').show();
        }).on('ajaxStop', function () {
            $('#totalonhandcash').text('Total Running Collection: ' + '₱' + totalOnHandCash());
            $('#loading').hide();
        });
        
        function totalOnHandCash() {
            var total = 0;
            $('#records tr').each(function() {
                var cohstatus = $(this).children('td:nth-child(2)').text();
                if (cohstatus == 'RECEIVED') {
                    var onhandcash = parseFloat($(this).children('td:nth-child(9)').text().replace('Cash on Hand: ', '').replace(',', '').replace('.00', ''));
                    total += onhandcash;
                }
            });
            return total.toLocaleString('en-US', {minimumFractionDigits: 2});
        }

        $(document).ready(function() {
        $(document).on('contextmenu',function(e) {
            e.preventDefault();
        });
        $(document).on('contextmenu', '#records tbody tr', function(e) {
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
                    url: 'loadrecords.php',
                    method: 'GET',
                    data: {filterdate: filterdate},
                    success: function(data) {
                        $('#recordtable').html(data);
                    }
                });
            } else {
                $.ajax({
                    url: 'loadrecords.php',
                    method: 'GET',
                    success: function(data) {
                        $('#recordtable').html(data);
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
        
        loadRecords(); // Load all dates by default
        
        setInterval(function() {
            if ($('#selectalldate').is(':checked')) {
                loadRecords();
            } else {
                var filterdate = $('#filterdate').val();
                loadRecords(filterdate);
            }
        }, 10000); 

    });

</script>
</body>
</html>
