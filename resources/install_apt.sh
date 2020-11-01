PROGRESS_FILE=/tmp/scan_ip/in_progress
OUI_FILE=/tmp/scan_ip/dependancy
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
echo 0 > ${PROGRESS_FILE}
apt-get update
sudo apt-get -y install arp-scan
echo 70 > ${PROGRESS_FILE}
sudo apt-get install libwww-perl
echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}