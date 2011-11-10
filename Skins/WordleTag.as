﻿package Skins {		import flash.display.*;	import flash.text.*;	import flash.geom.*;		public class WordleTag extends Sprite {				// Constants:		// Public Properties:				public var label:String='';		public var font:String='';		public var color:uint=0;		public var angle:Number=0;		public var size:Number=10;		public var index:uint=0;				// Private Properties:				private var textField:TextField;		private var textFormat:TextFormat;		private var container:Sprite;			// Initialization:		public function WordleTag() { }			// Public Methods:				public function render():void {						if(textField==null) setup();						while(angle>180) angle-=360;			while(angle<-180) angle+=360;						textFormat.color=color;			textFormat.size=size;			textFormat.font=FatCloud.fontName(font);						textField.defaultTextFormat=textFormat;			textField.text=label;						textField.x=textField.width/-2;			textField.y=textField.height/-2;			container.rotation=angle;					}				public function mouseIsOver():Boolean {			return container.getBounds(container).containsPoint(new Point(container.mouseX,container.mouseY));		}				public function textBounds():Rectangle {			return textField.getBounds(textField);		}				public function surfaceArea():Number {			return textField.width*textField.height;		}				// Protected Methods:				private function setup() {						container=new Sprite();			addChild(container);						textFormat=new TextFormat();			textField=new TextField();						container.addChild(textField);						textField.antiAliasType=AntiAliasType.ADVANCED;			textField.autoSize=TextFieldAutoSize.LEFT;			textField.embedFonts=true;			textField.multiline=false;			textField.selectable=false;			textField.wordWrap=false;			textField.sharpness=-150;			textField.thickness=10;						textFormat.align=TextFormatAlign.LEFT;					}	}	}