<?php

namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Facebook\WebDriver\Chrome\ChromeOptions;

class PostTweet extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'post:tweet';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Creates new tweet';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $weather_tweet = $this->getTweetText();

        $this->postTweetOnTwitter($weather_tweet);
    }

    public function getTweetText()
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get('https://api.openweathermap.org/data/2.5/weather?q=Vienna&units=metric&appid=lol');
        $response = $request->getBody();

        $data = json_decode($response);

        $weather_tweet = "Derzeitige Temperatur: " . $data->main->temp . "°С, \nGefühlte Temperatur: " . $data->main->feels_like . "°С, \nWindgeschwindigkeit: " . $data->wind->speed . " m/s. Bewölkung: " . $data->clouds->all . "%, \num " . Carbon::now('Europe/Vienna')->toTimeString() . " Uhr \n#Wien #WetterWien ";
        return $weather_tweet;
    }

    public function postTweetOnTwitter($tweet)
    {
        $this->browse(function ($browser) use ($tweet) {

            $browser->visit('https://twitter.com/login')
                ->pause(2000);


            $browser->type('session[username_or_email]', env('twitter_username'))
                ->type('session[password]', env('twitter_password'))
                ->pause(1000)
                ->click('div[data-testid="LoginForm_Login_Button"]')
                ->pause(3000);

            // if ($browser->element('#challenge_response') != null) {
            //     $browser->value('#challenge_response', env('challenge_response'))
            //         ->click('#email_challenge_submit')
            //         ->pause(3000);
            // }

            //Post Tweet
            $browser
                ->assertSee('Home')
                ->pause(1000)
                ->visit('https://twitter.com/compose/tweet')
                ->pause(2000)
                ->click('.public-DraftStyleDefault-block')
                ->keys(".public-DraftStyleDefault-block", $tweet)
                ->pause(4000)
                ->click('div[data-testid="tweetButton"]')
                ->pause(3000);
        });
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->everyThirtyMinutes();
    }
}
