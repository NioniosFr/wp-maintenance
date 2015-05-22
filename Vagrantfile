# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    # All Vagrant configuration is done here. The most common configuration
    # options are documented and commented below. For a complete reference,
    # please see the online documentation at vagrantup.com.

    config.vm.provider "virtualbox" do |v|
        v.gui = false
       # v.customize ["modifyvm", :id, "--memory", "1024"]
        v.memory = 384
        v.cpus = 1
    end

    config.vm.boot_timeout = 2500
    # Every Vagrant virtual environment requires a box to build off of.
    config.vm.box = "web-dev"
    config.ssh.forward_agent = false
    config.ssh.insert_key = false


    # Stand alone VM for :D Native :D python development!!
    config.vm.define "dev", primary: true do |dev|
        dev.vm.hostname = "wp-maintenance"
        dev.vm.network "private_network", ip: "192.168.50.50"
        dev.vm.synced_folder "D:/Workspace/php", "/home/vagrant/php"
        #dev.vm.provision :reload
    end

    # Share an additional folder to the guest VM. The first argument is
    # the path on the host to the actual folder. The second argument is
    # the path on the guest to mount the folder. And the optional third
    # argument is a set of non-required options.
    # config.vm.synced_folder "../data", "/vagrant_data"

    #
    # View the documentation for the provider you're using for more
    # information on available options.

end
