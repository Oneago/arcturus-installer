# Oneago PHP Project Installer

## Installation
Install composer in your pc and run:

```
composer global require oneago/installer
```
Next steep depends of your OS

### Mac OS X
````
nano ~/.bash_profile
````
add into new line `export PATH="$HOME/.composer/vendor/bin:$PATH"`

### Windows

Add into Windows system variables this `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`

### Linux distributions
````
nano ~/.bashrc
````
add into new line `$HOME/.config/composer/vendor/bin` or `$HOME/.composer/vendor/bin`

finally run 

```
source ~/.bashrc
```
