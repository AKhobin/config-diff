# config-diff
Generates a modelization difference between remote and local apps

## Installation guide

1. Clone a repository
```bash
git clone git@github.com:mkardakov/config-diff.git
```
2. Install dependencies using composer
```bash
/path/to/composer install
```
If the step №2 has been successful you will find **config-diff.phar** into your working copy dir

3. Add execute bit to your phar:
```bash
chmod +x config-diff.phar
```
4. Create a symlink to make your updates and life easier

```
sudo ln -s `pwd`/config-diff.phar /usr/local/bin/config-diff
```

## How to use

Take a look at help page and it will show you available commands and arguments:
```
config-diff --help
```
```
Options:
  -u, --svn-username=SVN-USERNAME  Svn username to DCP repo
  -p, --svn-password=SVN-PASSWORD  Svn password to DCP repo
  -l, --local=LOCAL                The absolute path to version.inc of local application
  -r, --remote[=REMOTE]            The url to svn repo with compared app
  -o, --out[=OUT]                  Path to output file
  -h, --help                       Display this help message
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi                       Force ANSI output
      --no-ansi                    Disable ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### Example 1. Get difference with default core model:
```
/path/to/config-diff --local=/var/www/determine/www/workspace/appli/product/version.inc --svn-username=admin --svn-password=admin
```

### Example 2. Get difference with specific remote (qualif4) app and dump result into a file /tmp/my_diff.php:
```
/path/to/config-diff --local=/var/www/determine/www/workspace/appli/product/version.inc --svn-username=admin --svn-password=admin 
--remote=https://tom.b-pack.com/svn/appli/q/qualif4/trunk/version.inc -o/tmp/my_diff.php

```
