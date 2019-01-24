echo Clearing previous build
rm ../PowerOf10Dist/*
echo Building new Angular app
cd results-scraper/
ng build --prod
echo Copying Angular app into distribution folder
cp dist/results-scraper/*.* ../../PowerOf10Dist/
cd ..
echo Copying PHP service into distribution folder
cp *.php ../PowerOf10Dist/
