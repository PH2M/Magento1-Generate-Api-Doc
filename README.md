# Magento 1 REST API documentation generator

Magento PHP script for generate your Magento API REST documentation.
![Latest version](https://img.shields.io/badge/latest-v0.0.1-red.svg)
![PHP >= 5.3](https://img.shields.io/badge/php-%3E=5.3-green.svg)
![Magento 1.9.3](https://img.shields.io/badge/magento-1.9.3-blue.svg)

## Installation & Usage

1. Generate input files
- Download the file `generateApiDoc.php` and past it on your Magento's `shell` directory
- Create `api/input` and `api/doc` directory on your Magento's
- Run this script with `php generateApiDoc.php` (Magento need to be up for working)

after running, this script create 2 new files on your `api/input` directory. This file help you to generate the [apiDoc](https://github.com/apidoc/apidoc) 

2. Generate doc with [apiDoc](https://github.com/apidoc/apidoc) 
- With Docker :
    - In your Magento's root directory, run `docker run --rm -v $(pwd)/api:/home/node/apidoc apidoc/apidoc -o doc -i input` (`$(pwd)` is for Linux, for other system see https://stackoverflow.com/questions/41485217/mount-current-directory-as-a-volume-in-docker-on-windows-10#answer-41489151
- With Npm :
    - You need to install https://github.com/apidoc/apidoc
    - In your Magento's root directory, run `apidoc -i api/input/ -o api/doc/`
    
You can now access to your Magento 1 REST API documentation on {MAGENTO_BASE_URL}/api/doc/index.html

## Contributing
This is a collaborative project. [Pull requests](https://github.com/PH2M/Magento1-Generate-Api-Doc/pulls) are welcome