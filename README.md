This project was created purely for testing. In this regard, a file with “secret” information and so on was added. (The information is not secret, but test information. So anyone can use it.)
Spend time - aboud 5h. Most of the time was spent for understanding what especially an old code do and how :-)

# Notes:

In most cases I'm getting an error, when tried to get data from lookup.binlist.net.
Even I tried to use just Chrome, it does not working (See screenshots/site_unavailable.png) file.
As a result, we have lot of incomplete calculation.

# Local setup:

To get project ready, you need to use Docker. After Docker installed, you should navigate to the folder, where a repository was previous cloned and folow next commands:
1. Start required containers:
```
docker-compose up -d
```

2. To enter to the Composer container and install all required Composer packages, you can use:
```
docker exec -it composer_app sh
composer install
```

# Launch application itself:

After all preparation and have all required Composer packages installed, you able to launch application itself. To do that, please follow next commands:
1. To enter to the PHP container, use:
```
docker exec -it php_app sh
```

2. To run app, use:
```
php app.php input.txt
```

# Run tests:
To run an unit tests, you can follow:
To enter to the Composer container, you can use:
```
docker exec -it composer_app sh
```

In the folder where project is located.

After you 're get Bash appeared, to run tests itself - just use:
```
composer test
```
