
Name: app-resource-report
Epoch: 1
Version: 1.4.8
Release: 1%{dist}
Summary: Resource Report
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
The Resource Report includes information on load, memory usage and running processes.

%package core
Summary: Resource Report - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-reports-core >= 1:1.4.3
Requires: app-reports-database-core >= 1:1.4.8
Requires: app-tasks-core

%description core
The Resource Report includes information on load, memory usage and running processes.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/resource_report
cp -r * %{buildroot}/usr/clearos/apps/resource_report/

install -D -m 0644 packaging/app-resource-report.cron %{buildroot}/etc/cron.d/app-resource-report
install -D -m 0755 packaging/resource2db %{buildroot}/usr/sbin/resource2db

%post
logger -p local6.notice -t installer 'app-resource-report - installing'

%post core
logger -p local6.notice -t installer 'app-resource-report-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/resource_report/deploy/install ] && /usr/clearos/apps/resource_report/deploy/install
fi

[ -x /usr/clearos/apps/resource_report/deploy/upgrade ] && /usr/clearos/apps/resource_report/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-resource-report - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-resource-report-core - uninstalling'
    [ -x /usr/clearos/apps/resource_report/deploy/uninstall ] && /usr/clearos/apps/resource_report/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/resource_report/controllers
/usr/clearos/apps/resource_report/htdocs
/usr/clearos/apps/resource_report/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/resource_report/packaging
%exclude /usr/clearos/apps/resource_report/tests
%dir /usr/clearos/apps/resource_report
/usr/clearos/apps/resource_report/deploy
/usr/clearos/apps/resource_report/language
/usr/clearos/apps/resource_report/libraries
/etc/cron.d/app-resource-report
/usr/sbin/resource2db
