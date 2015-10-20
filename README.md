# REDCap-Hooks
This is a framework for deploying hooks in REDCap on a global or per-project basis.

This code is covered by a standard GPL license which means you can't hold me liable :-)  In other words, it is YOUR responsiblity to ensure that the use of this code or any modifications to it meet your institutional security guidelines.  Some of the code in this repository is intended as example code for learning REDCap and not necessarily as production-ready code.  In other words, use at your own risk.  Please also leave attribution to this repository in the code and push back and updates and enhancements.

## Using this Framework
The code contained in this repository is typically installed as a nested subfolder off your root redcap directory.
* redcap_vx.y.z
* edocs
* languages
* plugins
* hooks
  * framework (this repository belongs here!)
    * redcap_hooks.php (this is the file that should be referenced in your control center)_
  * server (this is a per-instance folder where you add hooks to your server and projects)
    * global (a folder for global hooks)
    * pidxx (a folder for project-specific hooks)

#### Downloading this framework
1. Start by creating the hooks folder in your root redcap directory
2. Download the repository (example using ssh method).  I then renamed the folder from redcap-hook-framework to framework.  This isn't necessary.
```bash
andy123$ pwd
/var/www/redcap
andy123$ mkdir hooks
andy123$ cd hooks
andy123$ pwd
/var/www/redcap/hooks
andy123$ git clone git@github.com/123andy/redcap-hook-framework.git
Cloning into 'redcap-hook-framework'...
andy123$ mv redcap-hook-framework framework
```
3. In your redcap control center, point to the redcap_hooks.php file.  In the example above, the path would be `/var/www/redcap/hooks/framework/redcap_hooks.php`
4. Create the 'server' folder inside your hooks folder if it doesn't already exist.  The reason for breaking this into a separate folder is so that you can version this separately from the framework files.
