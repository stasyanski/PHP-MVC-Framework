# PHP-MVC-Framework
A flexible and reusable PHP framework built on the MVC pattern, designed to simplify web development and eliminate the hassle of building websites from scratch.
It is build completely on PHP with no additional frameworks. This project was adapted and improved from a personal project, and I have left some functionality in the controllers which is not essential.
This can be easily removed or tweaked according to your needs.

## Functionality

- Autoloader, EntryPoint, Routing
- Database Object Access, Database Connection
- Template files
- .env Loading

Other non-essential functionality can be found in various controllers, such as creating user accounts.

## Deployment

- MariaDB dump provided in database.sql, so you can easily make a database schema.
- To access the admin portal, simply go to /admin/portal.

## Environment Variables

For ease of use, I added my own implementation of loading .env variables, removing the need to use a third party solution. 
To run this project, you will need to add the following environment variables to the .env file, located in DotEnv folder.

`DB_HOST`
`DB_NAME`
`DB_USERNAME`
`DB_PASSWORD`

## License
The MIT License lets you do almost anything you want with this project, even making and distributing closed source versions.

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
