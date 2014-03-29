@cls
@echo building...
@java -jar yuicompressor-2.4.jar ../source/.resource/index.css -o ../build/.resource/index.css
@java -jar yuicompressor-2.4.jar ../source/.resource/index.js -o ../build/.resource/index.js
@echo html compressor : http://htmlcompressor.com/compressor/
@echo done...
@pause