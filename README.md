# Fork CMS Compression Module

![tests](https://github.com/friends-of-forkcms/fork-cms-module-compression/workflows/run-tests/badge.svg)

## Description

The Fork CMS Compression module lets you compress PNG & JPG images on your website. Use this module to shrink images on your website,
so they will use **less bandwidth and your website will load faster**. The compression module uses the compression engine
of [TinyPNG](https://tinypng.com/) and [TinyJPG](https://tinyjpg.com/).

## Preview

Backend + statistics:

[ ![Image](http://i.imgur.com/NvjmRHy.gif "Backend") ](http://i.imgur.com/NvjmRHy.gif)

[ ![Image](http://i.imgur.com/pWGrfmem.png "Statistics") ](http://i.imgur.com/pWGrfme.png)

I did the test with 3 images (3264x2448 resolution) taken from my camera. I uploaded and inserted the photos on a Fork CMS page and used the compression module. The images total size went from 8.2MB to 1.5MB!

## Installation

1. Install the [Tinify API client for PHP](https://github.com/tinify/tinify-php) php package: `composer require tinify/tinify`
1. Upload the `/src/Backend/Modules/Compression` folder to your `/src/Backend/Modules` folder.
1. Browse to your Fork CMS backend.
1. Go to `Settings > Modules`. Click on the install button next to the Compression module.
1. Go to `Settings > Modules > Compression` to start using it.

## How to use it

1. Get a free API key (500 images/month for free) [here](https://tinypng.com/developers)
2. Go to `Settings > Modules > Compression` and enter your API key
3. In the tree structure, choose a few folders with images to compress. Press save. Then press the execute button to start compression.
4. Use a cronjob if you want to compress these images once in a while, or press the execute button to compress the images on the fly.

Note: We store a history of compressed files in the database with a checksum. By doing that, we can ignore files that already have been compressed and ignore them when a new compression task starts, to save your TinyJPG api credits.

## Bugs

If you encounter any bugs, please create an issue (or feel free to fix it yourself with a PR).

## Discussion

- Slack: [Fork CMS Slack channel](https://fork-cms.herokuapp.com)
- Twitter: [@jessedobbelaere](https://www.twitter.com/jessedobbelaere)
