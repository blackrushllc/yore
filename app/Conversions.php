<?php
/*
 ▄▄▄▄    ██▓    ▄▄▄       ▄████▄   ██ ▄█▀ ██▀███   █    ██   ██████  ██░ ██
▓█████▄ ▓██▒   ▒████▄    ▒██▀ ▀█   ██▄█▒ ▓██ ▒ ██▒ ██  ▓██▒▒██    ▒ ▓██░ ██▒
▒██▒ ▄██▒██░   ▒██  ▀█▄  ▒▓█    ▄ ▓███▄░ ▓██ ░▄█ ▒▓██  ▒██░░ ▓██▄   ▒██▀▀██░
▒██░█▀  ▒██░   ░██▄▄▄▄██ ▒▓▓▄ ▄██▒▓██ █▄ ▒██▀▀█▄  ▓▓█  ░██░  ▒   ██▒░▓█ ░██
░▓█  ▀█▓░██████▒▓█   ▓██▒▒ ▓███▀ ░▒██▒ █▄░██▓ ▒██▒▒▒█████▓ ▒██████▒▒░▓█▒░██▓
░▒▓███▀▒░ ▒░▓  ░▒▒   ▓▒█░░ ░▒ ▒  ░▒ ▒▒ ▓▒░ ▒▓ ░▒▓░░▒▓▒ ▒ ▒ ▒ ▒▓▒ ▒ ░ ▒ ░░▒░▒
▒░▒   ░ ░ ░ ▒  ░ ▒   ▒▒ ░  ░  ▒   ░ ░▒ ▒░  ░▒ ░ ▒░░░▒░ ░ ░ ░ ░▒  ░ ░ ▒ ░▒░ ░
 ░    ░   ░ ░    ░   ▒   ░        ░ ░░ ░   ░░   ░  ░░░ ░ ░ ░  ░  ░   ░  ░░ ░
 ░          ░  ░     ░  ░░ ░      ░  ░      ░        ░           ░   ░  ░  ░
      ░                  ░
Copyright (C) 2024, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com

/app/Conversions.php - Push and Pull Site Data = Pages to Data / Data to Pages

This class is used to convert all domains, pages and data to "Site Data".  The Yore website can exist as static files
or be stored as data in a variety of formats: SQL, JSON, even dBASE or a Google sheet, allowing you to edit the files
and then convert to data storage, or use a front end editor to built the site directly into the data source, but then
export that data source to static files.

Yore can render the site using the static files or directly from the data source.  The advantage to using the data
source is that multiple Yore servers can render the same site by reading the common data source without having to
deploy updates to the server.  This is one of the fundamental features that makes Yore a unique framework!

*/


 ######   #######  ##    ## ##     ## ######## ########   ######  ####  #######  ##    ##  ######
##    ## ##     ## ###   ## ##     ## ##       ##     ## ##    ##  ##  ##     ## ###   ## ##    ##
##       ##     ## ####  ## ##     ## ##       ##     ## ##        ##  ##     ## ####  ## ##
##       ##     ## ## ## ## ##     ## ######   ########   ######   ##  ##     ## ## ## ##  ######
##       ##     ## ##  ####  ##   ##  ##       ##   ##         ##  ##  ##     ## ##  ####       ##
##    ## ##     ## ##   ###   ## ##   ##       ##    ##  ##    ##  ##  ##     ## ##   ### ##    ##
 ######   #######  ##    ##    ###    ######## ##     ##  ######  ####  #######  ##    ##  ######


namespace App;

class Conversions
{

    // TODO:
    //  --dtf - Data to File - Convert the common data array to the site files
    //  --ftd - File to Data - Create a common data array from the site files (it has to be an array)
    //  --source <data source> - Set the data source for site data (mySQL, Github, etc)
    //  --push - Write site data to the data source after creating
    //  --fetch - Fetch the site data from the data source before converting
    //  --overwrite the data source completely (default is to update) ??? maybe

    // Just for fun: Write this in Python too!

}