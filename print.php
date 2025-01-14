<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['branch_id'])) {
    header("Location: login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];
$id = $_SESSION['user_id'];
$id = $_GET['id'];
$query = "SELECT qi.id, qi.queueno, qi.branchid, qi.type, qi.clientname, qi.loanamount, qi.totalbalance, qi.cashonhand, qi.cashonhandstatus, qi.activenumber, qi.status, qi.date, qi.datereleased, qi.maturitydate, qi.accinterest, qi.remainingbalance, qi.remarks, qi.note, qi.attachname, b.branchname FROM queueinfo qi LEFT JOIN branch b ON qi.branchid = b.id WHERE qi.id = $id";

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<style>

        .mt1 {
            margin-top: 50px;
        }

        .br-pagebody {
            margin-left: auto;
            margin-right: auto;
            max-width: 1100px;
            font-family: sans-serif;
        }

        .br-section-wrapper {
            background-color: #fff;
            padding: 20px;
            margin-left: 0px;
            margin-right: 0px;
            box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.21);
        }

        .queue {
            max-width: 100%;
            max-height: 500px;
            overflow: auto;
        }

        .counter {
            max-height: 285px;
            overflow: auto;
        }

        .disabled {
            text-decoration: line-through;     
        }

        p {
            margin: 5px;
            margin-bottom: 0px;
        }

        .fileThumbnail {
          width: 100%;
          height: 100%;
          max-width: 950px;
          max-height: 450px;
          margin-bottom: 10px;
          border: 1px solid #e5e5e5;
        }

        @media print {
            body {
                background-color: white;
            }
        }
    </style>
<body>
    <div class="mt1 d-print-none"></div>
    <div class="br-pagebody">
        <div class="br-section-wrapper">
            <div class="row">
                <div class="col">
                    <span><b>Branch:</b> <?= $row['branchname'] ?></span>
                </div>
                <div class="col">
                    <span><b>Name:</b> <?= $row['clientname'] ?></span>
                </div>
                <div class="col">
                    <span><b>Loan Amount:</b> <?= number_format($row['loanamount'], 2, '.', ',') ?></span>
                    <input type="hidden" id="loanamount" value="<?= $row['loanamount'] ?>">
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <span><b>Date Released:</b> <?= date('F j, Y', strtotime($row['datereleased'])) ?></span>
                </div>
                <div class="col">
                    <span><b>Maturity Date:</b> <?= date('F j, Y', strtotime($row['maturitydate'])) ?></span>
                    <input type="hidden" id="maturitydate" value="<?= $row['maturitydate'] ?>">
                </div>
                <div class="col text-primary font-weight-bold">
                    <span><b>Overall Balance:</b> </span><span class="text-dark"><?= number_format($row['totalbalance'], 2, '.', ',') ?></span>
                    <input type="hidden" id="remainingbalance" value="<?= $row['totalbalance'] ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <span><b>On-hand Cash:</b> <?= number_format($row['cashonhand'], 2, '.', ',') ?></span>
                </div>
                <div class="col-4">
                    <span><b>Contact No:</b> <?= $row['activenumber'] ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-4 text-danger font-weight-bold">
                    <span><b>Accrued Interest:</b> </span><span class="text-dark" id="accinterest"></span>
                </div>
                <div class="col-6 text-danger font-weight-bold">
                    <span><b>Total Balance w/ Accrued Interest:</b> </span><span class="text-dark" id="totalbalance"></span>
                </div>
            </div>

            <span><b>Remarks:</b> <?= $row['remarks'] ?></span>
            <hr>
            <img class="form-control form-control-sm fileThumbnail mx-auto d-block" id="fileThumbnail" src="<?php echo $row['attachname']; ?>" alt="File Thumbnail">
            <div class="text-right">
                <button class="btn btn-sm btn-primary d-print-none" onclick="window.print();">Print</button>
                <a href="dashboard.php" class="btn btn-sm btn-danger d-print-none">Close</a>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script>
    $(document).ready(function(){
        var loanamount = $('#loanamount').val();
        var loanamount = loanamount.replace(/,/g, '');
        var remainingbalance = $('#remainingbalance').val();
        var remainingbalanceformatted = remainingbalance.replace(/,/g, '');
        var maturitydate = $('#maturitydate').val();
        var today = new Date();
        var diffTime = Math.abs(today - new Date(maturitydate));
        var diffMonths = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30)); 
        var diffMonths = diffMonths - 1;
        var accinterest = (loanamount * 0.06) * diffMonths;
        var accinterestformatted = parseFloat(accinterest).toLocaleString('en-US',{minimumFractionDigits: 2});
        var totalbalance = parseFloat(remainingbalanceformatted) + parseFloat(accinterest);
        var totalbalanceformatted = parseFloat(totalbalance).toLocaleString('en-US',{minimumFractionDigits: 2});
        $('#totalbalance').text(totalbalanceformatted);
        $('#accinterest').text(accinterestformatted);
        if (accinterest == "NaN") {
            $('#accinterest').text("0.00"); 
        }

        const fileThumbnail = document.querySelector('#fileThumbnail');
        if (fileThumbnail.src.endsWith('.pdf')) {
            const pdfLink = fileThumbnail.src;
            const loadingTask = pdfjsLib.getDocument(pdfLink);
            loadingTask.promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    const viewport = page.getViewport({ scale: 1.0 });
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise.then(function() {
                        fileThumbnail.src = canvas.toDataURL('image/png');
                    });
                });
            });
        }
        });
</script>
</body>
</html>