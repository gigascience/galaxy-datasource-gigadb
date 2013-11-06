#
# Cookbook Name:: main
# Recipe:: default
#
# Copyright 2012, Cogini
#
# All rights reserved - Do Not Redistribute
#

include_recipe 'php::module_pgsql'
include_recipe 'python'
include_recipe 'nodejs'
include_recipe 'postgresql::server'


app_user = node[:gigadb][:app_user]
db = node[:gigadb][:db]
db_user = db[:user]
site_dir = node[:gigadb][:site_dir]
python_env = node[:gigadb][:python][:virtualenv]


# Install sphinx

sphinx_rpm = "#{Chef::Config[:file_cache_path]}/sphinx-2.0.6-1.el5.i386.rpm"

remote_file sphinx_rpm do
    source 'http://centos.alt.ru/repository/centos/5/i386/sphinx-2.0.6-1.el5.i386.rpm'
    action :create_if_missing
end

execute "yum install -y --nogpgcheck #{sphinx_rpm}"


pgsql_user db_user do
    password db[:password]
end

pgsql_db db[:database] do
    owner db_user
end


user app_user do
    home "/home/#{app_user}"
    shell '/bin/bash'
    supports :manage_home => true
    action :create
end


[node[:gigadb][:log_dir], python_env].each do |dir|
    directory dir do
        action :create
        recursive true
    end
end


yii_framework '1.1.10'


template "#{site_dir}/index.php" do
    source 'yii-index.php.erb'
    mode '0644'
end

template "#{site_dir}/protected/config/local.php" do
    source 'yii-local.php.erb'
    mode '0644'
end

template "#{site_dir}/protected/config/db.json" do
    source 'yii-db.json.erb'
    mode '0644'
end

template '/etc/sphinx/sphinx.conf' do
    source 'sphinx.conf.erb'
    mode '0664'
end


execute "#{site_dir}/protected/scripts/init_perms.sh"


%w{ postgresql-devel }.each do |pkg|
    package pkg do
        action :install
    end
end


python_virtualenv python_env do
    action :create
    interpreter 'python2.6'
end

bash 'install python packages' do
    code <<-EOH
        . #{python_env}/bin/activate
        pip install -r #{site_dir}/protected/schema/requirements.txt
    EOH
end


execute 'npm install -g less'
