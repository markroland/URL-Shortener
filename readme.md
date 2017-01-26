# URL Shortening App

A URL-shortening web service

This project is documented at http://markroland.com/project/url-shortener

## Copyright

    Copyright 2011 Mark Roland.
    Released under the MIT license.

## System Recommendations

 - CentOS 6.7
 - Apache 2.2
 - PHP >= 5.6
 - MySQL 5.5

## Development Installation

This project runs from a [Vagrant](https://www.vagrantup.com) environment. In order to start using
it you will need Vagrant and VirtualBox installed.

After installation of Vagrant and VirtualBox you should be able to "vagrant up" from the working
directory. This will provision the machine with the LAMP stack necessary to run this web site.

NOTE: In order for the VirtualBox GuestAdditions plugin to work an initial "yum upgrade" and re-login will
probably be required.

```sh
yum upgrade
```

After upgrading the base box, then run the installation script:

```sh
    sh /vagrant/application/src/scripts/install.sh
```

## Usage

### Redirects

#### Valid HTTP redirect:
```
curl -I -L "http://localhost/google"
```

#### Query String Attached replacement test
```
curl -i -L "http://localhost/qsa-test?qsa=1&foo=bar"
```

#### Referral tracking test
```
curl -i -L "http://localhost/google?ref=sidebar"
```

#### Valid Javascript redirect
```
curl -i -L "http://localhost/yahoo"
```

#### Invalid redirect
```
curl -i -L "http://localhost/abc"
```

### API

Note: The API key must match the value in application/data/sample-credentials

# Get Shortcuts
```
curl "http://localhost/api/get_shortcuts.json?api_key=aCyNxnbgPUG3fuPfuLqutTqi2RTZ2W4Q"
```

```
curl "http://localhost/api/get_shortcuts.json?api_key=aCyNxnbgPUG3fuPfuLqutTqi2RTZ2W4Q&client_id=1"
```

#### Create Shortcut
```
curl -v "http://localhost/api/create_short_url.json" \
  -X POST \
  -d "api_key=aCyNxnbgPUG3fuPfuLqutTqi2RTZ2W4Q" \
  -d "shortcut=X" \
  -d "destination=http%3A%2F%2Fgoogle.com"
```

#### Create Shortcut when exists
```
curl -v "http://localhost/api/create_short_url.json" \
  -X POST \
  -d "api_key=aCyNxnbgPUG3fuPfuLqutTqi2RTZ2W4Q" \
  -d "shortcut=X" \
  -d "destination=http%3A%2F%2Fgoogle.com"
```

#### Update Shortcut
```
curl -v "http://localhost/api/short_url.json" \
  -X PUT \
  --user aCyNxnbgPUG3fuPfuLqutTqi2RTZ2W4Q: \
  -d "shortcut=X" \
  -d "destination=http%3A%2F%2Fgoogle.com/update=1" \
  -d "set_referrer=1" \
  -d "client_id=1000"
```

```
curl -v "http://localhost/api/short_url.json" \
  -X PUT \
  --user aCyNxnbgPUG3fuPfuLqutTqi2RTZ2W4Q: \
  -H "Content-Type: application/json" \
  -d '{
  "shortcut": "X",
  "destination": "http://google.com/update=2",
  "set_referrer": "1",
  "client_id": "1001"
}'
```

## TODO:

 - Implement Phing build (build.xml)
 - Implement Continuous Integration with Jenkins (Jenkinsfile)
