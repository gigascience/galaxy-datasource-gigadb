default[:gigadb][:app_user] = 'gigadb'
default[:gigadb][:db][:database] = 'gigadb'
default[:gigadb][:db][:user] = 'gigadb'
default[:gigadb][:python][:build_dir] = '/home/gigadb/build'
default[:gigadb][:python][:virtualenv] = '/home/gigadb/.virtualenvs/gigadb'

case node[:environment]
when 'vagrant'
    default[:nginx][:expires] = '-1'
    default[:nginx][:sendfile] = 'off'
else
    default[:nginx][:expires] = 'max'
    default[:nginx][:sendfile] = 'on'
end
