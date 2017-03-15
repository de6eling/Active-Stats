## Active Stats

Active Stats is a web application that helps players, coaches, and fans, gain accurate relevant stats about the sport they love.

### Installing

Active Stats is a Laravel Web Application.  Laravel requires composer to run. 

Use the composer [installation and documentation here.](https://getcomposer.org/)

With composer installed we are ready to clone project onto the a desired machine.  Navigate to an appropriate directory and run the following command in the terminal:

```
git clone https://github.com/de6eling/Active-Stats.git
```
Navigate into the newly created directory and pull down its dependencies with the following command:
```
composer install
```
With all the dependencies installed the final step to create and manage the .env file.  Copy the .env.example file to use as a template with the following command:
```text
cp .env.example .env
```
In the .env set up the desired database variables and you're good to go!