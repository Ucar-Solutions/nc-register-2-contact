<!--
SPDX-FileCopyrightText: Ucar Solutions UG (haftungsbeschränkt) <info@ucar-solutions.de>
SPDX-License-Identifier: CC0-1.0
-->

# 🤝 Register To Contact App for Nextcloud

🌟 **Welcome to the Register To Contact app for Nextcloud!** This app automagically adds newly created users as contacts to all existing users, seamlessly integrating new members into your Nextcloud ecosystem.

![Register To Contact Logo](img/registertocontact-logo.jpeg)
---

## 📦 Installation

To add this app to your Nextcloud instance, simply place it in the `nextcloud/apps/` directory.

---

## 🔨 Building the App

Building this app is a breeze with the provided Makefile. Ensure you have the prerequisites and then run the following command:

```bash
make

This requires the following things to be present:
* make
* which
* tar: for building the archive
* curl: used if phpunit and composer are not installed to fetch them from the web
* npm: for building and testing everything JS, only required if a package.json is placed inside the **js/** folder

The make command will install or update Composer dependencies if a composer.json is present and also **npm run build** if a package.json is present in the **js/** folder. The npm **build** script should use local paths for build systems and package managers, so people that simply want to build the app won't need to install npm libraries globally, e.g.:

**package.json**:
```json
"scripts": {
    "test": "node node_modules/gulp-cli/bin/gulp.js karma",
    "prebuild": "npm install && node_modules/bower/bin/bower install && node_modules/bower/bin/bower update",
    "build": "node node_modules/gulp-cli/bin/gulp.js"
}
```


## 🚀 Publishing to the App Store

Ready to share your app with the world? Here’s how:

Secure an account on the Nextcloud App Store [App Store](http://apps.nextcloud.com/).
Execute:


    make && make appstore

The generated archive will be located in build/artifacts/appstore, ready for you to upload to the App Store.

## 🧪 Running Tests

Leverage the Makefile to run all tests effortlessly:


    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
