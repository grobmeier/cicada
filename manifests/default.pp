
exec { 'apt-get update':
  command => 'apt-get update',
  path    => '/usr/bin/',
  timeout => 60,
  tries   => 3,
}

class { 'apt':
  always_apt_update => true,
}

package { ['python-software-properties']:
  ensure  => 'installed',
  require => Exec['apt-get update'],
}

file { '/home/vagrant/.bash_aliases':
  ensure => 'present',
  source => 'puppet:///modules/puphpet/dot/.bash_aliases',
}

package { ['build-essential', 'vim', 'curl']:
  ensure  => 'installed',
  require => Exec['apt-get update'],
}

class { 'apache': 
  require       => Exec['apt-get update'],
}

apache::dotconf { 'custom':
  content => 'EnableSendfile Off',
}

apache::module { 'rewrite': }

apache::vhost { 'cicada.local':
  server_name   => 'cicada.local',
  serveraliases => ['www.cicada.local'],
  docroot       => '/var/www/cicada',
  port          => '80',
  env_variables => [],
  priority      => '1',
}

apt::ppa { 'ppa:ondrej/php5':
  before  => Class['php'],
}

class { 'php':
  service => 'apache',
  require => Package['apache'],
}

php::module { 'php5-mysql': }
php::module { 'php5-cli': }
php::module { 'php5-curl': }
php::module { 'php5-intl': }
php::module { 'php5-mcrypt': }

class { 'php::devel':
  require => Class['php'],
}

class { 'php::pear':
  require => Class['php'],
}



class { 'xdebug':
  service => 'apache',
}

xdebug::config { 'cgi':
  remote_autostart => '0',
  remote_port      => '9000',
}
xdebug::config { 'cli':
  remote_autostart => '0',
  remote_port      => '9000',
}

php::pecl::module { 'xhprof':
  use_package => false,
}

apache::vhost { 'xhprof':
  server_name => 'xhprof',
  docroot     => '/var/www/xhprof/xhprof_html',
  port        => 80,
  priority    => '1',
  require     => Php::Pecl::Module['xhprof']
}


class { 'php::composer': }

php::ini { 'php':
  value   => ['date.timezone = "Europe/Berlin"'],
  target  => 'php.ini',
  service => 'apache',
}
php::ini { 'custom':
  value   => ['display_errors = On', 'error_reporting = -1'],
  target  => 'custom.ini',
  service => 'apache',
}

class { 'mysql':
  root_password => 'root',
  require       => Exec['apt-get update'],
}

mysql::grant { 'cicada':
  mysql_privileges     => 'ALL',
  mysql_db             => 'cicada',
  mysql_user           => 'cicada',
  mysql_password       => 'cicada',
  mysql_host           => 'localhost',
  mysql_grant_filepath => '/home/vagrant/puppet-mysql',
}

class { 'phpmyadmin':
  require => Class['mysql'],
}

apache::vhost { 'phpmyadmin':
  server_name => 'phpmyadmin',
  docroot     => '/usr/share/phpmyadmin',
  port        => 80,
  priority    => '10',
  require     => Class['phpmyadmin'],
}

