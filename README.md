# dyndns-cloudflare-updater
A lightweight PHP script that updates your dns records at Cloudflare when pinged by your router.

You will need:
- A Cloudflare account (it doesn't matter whether it's free or a paid plan)
- A webspace with PHP support (just for this script)
- A router that can ping this updater (tested with FRITZ!Box 7490 by AVM)

## Configuration
### Step 1: Get your token
First of all you need a [Cloudflare API token](https://dash.cloudflare.com/profile/api-tokens) with specified permissions: 
- Zone / DNS / Edit
- Zone / DNS / Read

Select your zone in Zone Resources:
- Include / Specific zone / example.com

For security reasons it's recommended limit the access to your webserver's ip in "Client IP Address Filtering". You can copy and paste the token in the $config array (*token*). 

### Step 2: Set up your domain
Stay logged in on Cloudflare! After that you need to create A and AAAA records (e.g. dyndns.example.com) and you have to copy the Zone ID. The Zone ID is shown on the domain overview. You can adapt the Zone ID into the $config array.

### Step 3: Set up your router
Finalize the updater by inserting the credentials on your router settings. You need to paste a url like this:
https://example.com/updater.php?user=<username>&password=<pass>&host=<domain>&ip=<ipaddr>&ip6=<ip6addr>

User and password are stored unencrypted and without any hash in the $config array because the routers pass them on as a get variable anyway... So, you will need to set up them to.
