<p align="center">
  <a href="https://amplitude.com" target="_blank" align="center">
    <img src="https://static.amplitude.com/lightning/46c85bfd91905de8047f1ee65c7c93d6fa9ee6ea/static/media/amplitude-logo-with-text.4fb9e463.svg" width="280">
  </a>
  <br />
</p>

# Experiment PHP SDK
Amplitude PHP Server SDK for Experiment.

## Installation
```php
composer require amplitude/experiment-php-server
```

## Remote Evaluation Quick Start
```php
<?php
// (1) Initialize the experiment client
$experiment = new \AmplitudeExperiment\Experiment();
$client = $experiment->initializeRemote('<DEPLOYMENT_KEY>')

// (2) Fetch variants for a user
$user = \AmplitudeExperiment\User::builder()
    ->deviceId('abcdefg')
    ->userId('user@company.com')
    ->userProperties(['premium' => True])
    ->build();
$variants = experiment.fetch($user);

// (3) Access a flag's variant
$variant = $variants['FLAG_KEY']
if ($variant) {
    if ($variant->value == 'on') {
        // Flag is on
    } else {
        // Flag is off
    }
}
```

## Local Evaluation Quick Start

```php
<?php
// (1) Initialize the experiment client
$experiment = new \AmplitudeExperiment\Experiment();
$client = $experiment->initializeLocal('<DEPLOYMENT_KEY>');

// (2) Start the local evaluation client.
$client->start()->wait();

// (3) Evaluate a user.
$user = \AmplitudeExperiment\User::builder()
    ->deviceId('abcdefg')
    ->userId('user@company.com')
    ->userProperties(['premium' => true]) 
    ->build();

// Evaluate all flag variants
$allVariants = $client->evaluate($user);

// Evaluate a specific subset of flag variants
$specificVariants = $client->evaluate($user, [
  'my-local-flag-1',
  'my-local-flag-2',
]);

// (4) Access a flag's variant
$variant = $allVariants['FLAG_KEY']
if ($variant) {
    if ($variant->value == 'on') {
        // Flag is on
    } else {
        // Flag is off
    }
}
```

## More Information
Please visit our [Developer Center](https://www.docs.developers.amplitude.com/experiment/sdks/php-sdk/) for more instructions on using our the SDK.

## Need Help?
If you have any problems or issues over our SDK, feel free to [create a GitHub issue](https://github.com/amplitude/experiment-php-server/issues/new) or submit a request on [Amplitude Help](https://help.amplitude.com/hc/en-us/requests/new).
