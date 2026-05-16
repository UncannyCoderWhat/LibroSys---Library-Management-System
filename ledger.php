<?php 
include "sidebar.php"; 
?>

<link rel="stylesheet" href="style.css">

<div class="main-content-container">
<div class="topbar">
        <img src="images/LibroSys.png" alt="Logo">
</div>

    <div class="section-header">
        <div class="header-left">
            <img src="images/lineMenu.png" class="menu-icon" alt="Menu">
            <h2>Ledger</h2>
        </div>

        <div class="header-right">
            <span>Admin</span>
            <img src="images/profile.png" class="profile-pic" alt="Admin Profile">
        </div>
    </div>

    <div class="ledger-top-cards">
        <div class="ledger-info-card">
            <div class="card-left">
                <img src="borrow.png" class="ledger-card-icon">

                <div class="left-text">
                   <h3>Currently Borrowed Books</h3>
                    <div class="borrow-count">208</div>
                </div>
            </div>
        </div>

        <div class="ledger-info-card">
            <div class="right-text">
               <h3>Ledger <br></h3>
               <h4>
                1 - 3 days late :  ₱50/day <br>
                4 - 10 days late :  ₱100/day <br>
                11+ days late :  ₱150/day
               </h4>
            </div>
            <img src="ledger.png" class="ledger-card-icon">
        </div>
    </div>

    <table class="ledger-table">
        <tr>
            <th>Borrowed By</th>
            <th>Book Borrowed</th>
            <th>Date Borrowed</th>
            <th>Due Date</th>
            <th>Date Returned</th>
            <th>Days Late</th>
            <th>Total Fine</th>
        </tr>

        <tr>
            <td>Juan Dela Cruz</td>
            <td>The Alchemist</td>
            <td>Oct 19, 1901</td>
            <td>Oct 25, 1901</td>
            <td>Nov 00, 1901</td>
            <td>00 days</td>
            <td>5000</td>
        </tr>

        <tr>
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
</div>
