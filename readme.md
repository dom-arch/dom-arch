# <a name="title">DOMArch</a>

<i>When a tiny [PHPDOM](https://github.com/Lcfvs/PHPDOM) demo becomes a true opensource framework</i>

A PHP core framework, based on the DOM and services oriented

## <a name="summary">Summary</a>
* [The contexts](#the-contexts)
* [Installation](#installation)
* [License](#license)

## <a name="the-contexts">The contexts :</a>

Actually, additionally to this core, DOMArch has 3 contexts :
* [website](https://github.com/dom-arch/website) : used for visitors (unidentified)
* [app](https://github.com/dom-arch/app) : used for members (identified)
* [service](https://github.com/dom-arch/service) : used to provide the data to the requiring other contexts

## <a name="installation">Installation :</a>

* Put all files, directly on your document root
* Enable the Apache Rewrite module
* Add a host, to your hosts file, for each context :
  * `domain.tld`
  * `app.domain.tld`
  * `service.domain.tld`
* Create a database for each context :
  * `domain-tld-website`
  * `domain-tld-app`
  * `domain-tld-service`
* Go to the `sql` directory, contained in each context directory, and execute each table script, in the related database
* In a shell, make these commands, in each context directory :
  * `composer install`
  * `php cli/setup.php`
* Go to http://domain.tld

(No virtualhosts needed, the generic .htaccess detects the host and redirects the requests to the directory with the same name)

(The previous names are, by example, for a fast first shot, you can change them, rename the directories and update the related config.json files, as you want)

## <a name="license">License :</a>
This project is MIT licensed.

Copyright 2015 Lcf.vs
