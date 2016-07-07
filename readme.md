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

* Enable the Apache Rewrite module
* Copy the [document-root-files](./document-root-files) contents, directly on your document root
* Rename the `domain.tld`, if needed
* Create or clone a context into the `entrypoints` directory, chosse the subdomain name as dirname

No virtualhosts needed, the generic .htaccess files detects the host and redirects the requests to the directory with the same name

The previous names are, by example, for a fast first shot, you can change them, rename the directories and update the related config.json files, as you want

## <a name="license">License :</a>
This project is MIT licensed.

Copyright © 2015 - 2016 Lcf.vs