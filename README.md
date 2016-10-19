# Redirect Checker
Simple tool for checking if your **http redirects** work as you want.

Installation
-
```
$ composer require antstudiocz/redirect-checker
```

Register DI extension:
```neon
extensions: 
    - Ant\RedirectChecker\DI\RedirectCheckerExtension
```

Add configuration:
```neon    
parameters: 
    redirect-checker:
        file: app/config/redirects.neon
```

Create file for example:
```neon
redirects:
    "https://www.antstudio.cz": http://www.antstudio.cz
```

Usage
-
```
$ php index.php app:redirect-checker:run
```

