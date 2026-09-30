### Default configuration

Configuration shipped in this directory is not tracked by Features. It is installed via a `hook_install()` implementation when the module is enabled.

This configuration is classified as default configuration that may be changed by individual sites, per https://wikis.utexas.edu/display/WCMS/Performing+configuration+updates+for+the+distribution.

For SaaS-type sites to inherit changes to this configuration, an update hook needs to be added that will programmatically load the configuration entity and update it.
