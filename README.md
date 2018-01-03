# Cloudflare DynDNS
*A flexible framework to update a Cloudflare DNS record automatically, via command line or HTTP request. Host your own DynDNS provider!*

## Reasoning
While there are a few DyDNS providers out there, most of them charge for their service. Considering I have Cloudflare's DNS, a server and my home router at hand anyway, I don't see a need to pay someone for such a minute service.  
Hosting your own DynDNS service usually implies also managing the DNS on your server, but then again - why take the risk of hosting a DNS server? Cloudflare has free plans and a fantastic API anyway.

This tool allows you to host your own DynDNS server that will proxy any requests to the Cloudflare API. That way, you can easily set up your router to update your record without all of the hassle.

## Features

 - REST API to view and modify DNS records for your zones
 - CLI application with the same feature set
 - Transparent on-the-fly encryption of your Cloudflare credentials, no additional accounts
 - Ready for any router
 - Handles IPv4 and/or IPv6 addresses

## Installation

### Using composer
*Notice: This package is not yet available on composer, so the following won't work for now.*

```bash
composer require radiergummi/dyndns
```

### Using git

```bash
git clone https://github.com/Radiergummi/dyndns
composer install
```

### Setting up your web server
I don't have an Apache at hand currently, so the following directions are for nginx only. If you're having trouble getting this to work on Apache, just [open an issue](https://github.com/Radiergummi/dyndns/issues/new).

`/etc/nginx/sites-available`:

```nginx
server {
  listen      80;
  server_name dyndns.YOUR-DOMAIN.COM;

  location / {
    return 301 https://$host$request_uri;
  }
}

server {
  listen      443 ssl http2;
  server_name dyndns.YOUR-DOMAIN.COM;

  // this is my SSL configuration, adapt for your use case
  include modules/certificates/dyndns.conf;
  include modules/ssl.conf;

  root  /var/www/dyndns;
  index index.php;

  location / {
    try_files $uri $uri/ @missing;
  }

  location @missing {
    rewrite (.*) /index.php;
  }

  location ~ .php$ {
    fastcgi_index index.php;
    include       modules/fastcgi.conf;
    fastcgi_pass  php;
  }
}
```

## Usage
This application works on both the CLI and via HTTP(S).  
The CLI application is available at `bin/dyndns`, while the web server will list all available API endpoints at `/`.

### Update the application secret
First thing to do is open up `index.php`. Here, you'll see a configuration section. The only thing required to change here is line 26: `$config->secret = 'Put your random secret string here';`
This ensures your API token can be encrypted securely.

### Obtain your Cloudflare API key
That's right, I said encrypted! To ensure your API token will never travel the network in plain text, you will need to encrypt it first. The API token is your Cloudflare password for API access, basically. To obtain it, navigate to [your Cloudflare profile](https://www.cloudflare.com/a/profile) and click on *View API Key* next to *Global API Key*. 

### Encrypt your API key
Open up your terminal and navigate to the application directory. There, execute the following:

```bash
bin/dyndns auth:encrypt {{your api key}}
```

I promise you that this command won't ever transmit your API key to anywhere outside of your server, it only encrypts it using OpenSSL. Never trust random dudes on the internet, though: Feel free to inspect the [command source](./src/app/Commands/EncryptCommand.php) to find out what it actually does. The code is well documented.

The command will print your encrypted token to the console. It might be pretty long, but that doesn't matter. Note it down somewhere. In case you loose that string, don't worry: You can always encrypt your API key again. If you do so, the encrypted string will look completely different, but still work. Magic :)

> Pro tip: If you intend to automate this, simply pass the `-q` switch. That will suppress anything but the plain text encrypted key.

### Try it out
Now that you have your encrypted key (we'll call that your *password* from now on), you can already update the DNS record for your DynDNS domain using your web browser. The URL looks like so:

<pre>
https://<b>your-dyndns-server.com</b>/zones/<b>{{your-cloudflare-zone}}</b>/<b>{{your-home-domain}}</b>/update?ipv4=<b>{{new-ip-address}}</b>
</pre>

Replace the values in brackets with your actual data:
 - `your-dyndns-server.com` is the domain where this application runs
 - `your-cloudflare-zone` is the Cloudflare zone your home domain runs under
 - `your-home-domain` is the domain you intend to dynamically update
 - `new-ip-address` is the new IP address
 
 This will open up a basic authentication prompt: Enter your Cloudflare email address for username and your *password* (the encrypted API key. I told you to note it down!).
After submitting that, you should see the JSON response saying: 
```json
{
  "status": "success",
  "message": "Record updated successfully"
}
```

That's it! Your record points to the new IP address.

### Set up your router
The last step is updating your router configuration. This largely depends on what kind of device you have there, but here's some general instructions:

Most routers have a field called `Update-URL` or similar, and some placeholders to insert in that URL. Make sure it looks more or less like this:

<pre>
https://<b>your-dyndns-server.com</b>/zones/<b>{{your-cloudflare-zone}}</b>/<b>{{your-home-domain}}</b>/update?ipv4=<b>{{new-ip-address}}</b>&ipv6=<b>{{new-ipv6-address}}</b>
</pre>

Fill the username and password fields just like you did in the basic authentication field above.

### Set up your own Linux box
If you have your own router based on Linux (congrats, never got motivated enough to do this) or just a Linux box in your network, you can install this application locally there, too: In that case, we'll use the CLI application again.  
The appropriate command is

```bash
bin/dyndns update \
    {{zone}} \
    {{hostname}} \
    --ipv4={{new-ipv4-address}} \
    --ipv6={{new-ipv6-address}} \
    --username={{cloudflare-email}} \
    --password={{encrypted-api-key}}
```

Both parameters, `--ipv4` (or `-4`) and `--ipv6` (or `-6`) are optional, so you can pass either or both.  
Set up a cron job to execute this command daily (but make sure to adjust the path correctly).  

### Using Windows
In theory, this should work on Windows, too. But I've got no way to test this currently, so if you run into any problems, again feel free to [open an issue](https://github.com/Radiergummi/dyndns/issues/new).

### Lean back and enjoy
That's it, you're done. Your IP address will be updated automatically, your DNS record points to your home network at all times.

## REST API Endpoints
You can view a dynamically generated list of these at the `/` page, too.  

| Method | Path                              | Description                        |
|--------|-----------------------------------|------------------------------------|
| GET    | `/`                               | Shows a list of all API endpoints  |
| GET    | `/zones`                          | Retrieves a list of all zones      |
| GET    | `/zones/{zone}`                   | Retrieves data from a single zone  |
| GET    | `/zones/{zone}/{hostname}`        | Retrieves a single host DNS record |
| PUT    | `/zones/{zone}/{hostname}/update` | Updates a DNS record. Must contain fields for 'ipv4' and/or 'ipv6' in the body (or params for GET). |
| GET    | `/zones/{zone}/{hostname}/update` | Updates a DNS record. Must contain fields for 'ipv4' and/or 'ipv6' in the body (or params for GET). |

*I know that the `/update` is a poor choice, but due to many routers only supporting GET requests, we need to discern between the hostname GET feature and the actual update.*

## CLI Commands
You can view a list of those using `bin/dyndns list`, too.

| Command        | Description             |
|----------------|-------------------------|
| `auth:encrypt` | Encrypts a password     |
| `auth:decrypt` | Decrypts a cipher       |
| `update`       | Updates a DynDNS record |


## Security considerations
There are some things you should be aware of: First, all records created by this tool won't be proxied by Cloudflare. That means the DNS exposes your real IP and can (**and will**) be scraped. Make sure whatever is available on that IP is protected as good as you can.  
Then, the API token. I went down the path of encryption to make sure you could *theoretically* also use this application via insecure HTTP, since an attacker would need access to your server and read the application secret to decrypt your API key. Please don't do that. Use HTTPS for everything you host, it's 2018, [Let's encrypt](https://letsencrypt.org) works stable by now.  
Even then, you should be careful with both your API key and the app secret. It's what protects you from someone manipulating your DNS, potentially causing real damage to you.

## Encryption details
The encryption uses OpenSSL with *AES-256-CBC* and should be [implemented reasonably secure](./src/app/Services/Authentication.php). Due to the usage of initialization vectors (IVs), you will receive different output each time you encrypt the same key. That's expected and well. Each of those ciphers will decrypt to your original key.

## Future development
I'm thinking of adding additional DNS backends to this tool, for example for a local BIND server or GoDaddy. This should be fairly possible using the application structure as it is currently (it'd require adding a new Service for the provider, then somehow defining that as the one to use via Configuration).

## Developer notes
**Static strings**
The code is thoroughly documented and uses practically no hard coded strings - all of them are defined as class constants so you can hardly break anything by changing those fields. 

**Unified infrastructure**
Basically, the `index.php` file is the entry point for both the web server and the CLI. It determines the SAPI mode and loads the appropriate kernel class. All of this is inspired by Laravel, but nowhere near as complex. Both HTTP routes and CLI commands can be defined in the appropriate `routes` file at `src/routes`, in `web.php` (HTTP) and `console.php` (CLI) respectively.  

**Inheritance**
There, you'll see both are added using a `Route` class - `WebRoute` and `ConsoleRoute`, which both inherit from `Route`. The whole application is structured like this, with the base class sharing the common properties, constants and methods.  

**Routing**
In those routes files, you can add your own routes using a simple mechanism: All routes require a `handler`. That is a Controller for web routes and a Command for console routes. This handler needs to be placed in the corresponding directory in the app folder, namely `src/app/Controllers` or `src/app/Commands`. The handler will be resolved by taking the string from the route property, uppercasing the first letter and appending "Console" or "Command" to it. Therefore, a handler named "foo" will be resolved to "FooController" or "FooCommand".  
You can also add a method by passing `foo@bar`, which will assume `FooController#bar()` as the action handler. If you omit the method, the default `index` will be used.  

**Lambda callback handlers**
Lastly, there's one specialty - you can also pass a callback instead of a string to the `handler` property. The callback will be wrapped in a special Controller/Command that simply executes the callback. These special handlers are called `LambdaController` and `LambdaCommand` and work practically the same way. They receive the same arguments as all Controllers/Commands do.

**Services and DI**
The Kernel class provides a minimal DI container implementation that manages service instances. Services are classes serving functionality to both Commands and Controllers, therefore working independently from them. They also have access to the kernel instance via `getKernel()`, so they can use all other services, configuration data and the logger.  
You can store constructor arguments that will be passed to the service constructor as soon as it is required. Of course there's also a `getFactory()` method to always retrieve a new instance with fresh args. Consumers can load a service using `$this->getKernel()->getService( Namespace\MyService::class )`. Instead of using string identifiers, I chose to go with the actual FQCN here.
