<?php

//Main cron jobs file, include all other cron jobs.

//Every minute
include('egeneration.php');
include('auction_management.php');
include('shield.php');