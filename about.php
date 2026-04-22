<?php
// about.php

require_once 'config.php';

include 'header.php';
?>

<!-- Page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">About</span>
    <h1>About VARO</h1>
    <p>VARO is a simple PHP and MySQL project for room booking. .</p>
</div>

<!-- Main about blocks -->
<div class="grid-two">
    <div class="card glass-card fade-card delay-1">
        <h2>What The Project Does</h2>
        <ul>
            <li>User registration and login</li>
            <li>Room list and booking form</li>
            <li>User profile with photo and stats</li>
            <li>Admin panel for room management</li>
        </ul>
    </div>


<!-- Simple contact/info block -->
<div class="card glass-card fade-card delay-3">
    <h2>Contact</h2>
    <p><strong>Project name:</strong> VARO</p>
    <p><strong>Type:</strong> Student room booking system</p>
</div>

<?php include 'footer.php'; ?>
