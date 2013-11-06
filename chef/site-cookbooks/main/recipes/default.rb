#
# Cookbook Name:: main
# Recipe:: default
#
# Copyright 2012, Cogini
#
# All rights reserved - Do Not Redistribute
#

include_recipe "apt"
include_recipe "yii"
include_recipe "php::fpm"
include_recipe "php::module_pgsql"
include_recipe "nginx"
include_recipe "postgresql::server"
include_recipe "python"
include_recipe 'nodejs'


execute 'npm install -g less'


pgsql_user "gigadb" do
    password "vagrant"
end

pgsql_db "gigadb" do
    owner "gigadb"
end

user "gigadb" do
    action :create
    supports :managed_home => true
end


directory node[:gigadb][:log_dir] do
    action :create
    recursive true
end

template "/etc/nginx/sites-available/gigadb" do
    source "nginx-gigadb.erb"
    mode "0644"
end

template "/vagrant/index.php" do
    source "yii-index.php.erb"
    mode "0644"
end

template "/vagrant/protected/config/local.php" do
    source "yii-local.php.erb"
    mode "0644"
end

template "/vagrant/protected/config/db.json" do
    source "yii-db.json.erb"
    mode "0644"
end

execute "/vagrant/protected/scripts/init_perms.sh"

nginx_site "gigadb" do
    action :enable
end


%w{libpq-dev}.each do |pkg|
    package pkg do
        action :install
    end
end

python_env = node[:gigadb][:python][:virtualenv]
build_dir = node[:gigadb][:python][:build_dir]

[build_dir, python_env].each do |dir|
    directory dir do
        action :create
        recursive true
    end
end

python_virtualenv python_env do
    action :create
end

node[:gigadb][:python][:packages].each do |pkg|
    python_pip pkg do
        action :install
        virtualenv python_env
    end
end

bash "install schemup" do
    cwd build_dir
    code <<-EOH
        . #{python_env}/bin/activate
        git clone https://github.com/brendonh/schemup.git
        cd schemup
        git fetch
        git checkout #{node[:gigadb][:python][:schemup][:version]}
        pip install .
    EOH
end
