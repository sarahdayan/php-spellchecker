<?php

declare(strict_types=1);

namespace PhpSpellcheck\Tests\TextProcessor;

use PhpSpellcheck\Text;
use PhpSpellcheck\TextProcessor\MarkdownRemover;
use PHPUnit\Framework\TestCase;

class MarkdownRemoverTest extends TestCase
{
    public function testShouldStripOutRemainingMarkdown()
    {
        $string = "*Javascript* developers are the _best_.";
        $expected = "Javascript developers are the best.";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldLeaveNonMatchingMarkdownMarkdown()
    {
        $string = "*Javascript* developers* are the _best_.";
        $expected = "Javascript developers* are the best.";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldLeaveNonMatchingMarkdownButStripEmptyAnchors()
    {
        $string = "*Javascript* [developers]()* are the _best_.";
        $expected = "Javascript developers* are the best.";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldStripHtml()
    {
        $string = "<p>Hello World</p>";
        $expected = "Hello World";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldStripAnchors()
    {
        $string = "*Javascript* [developers](https://engineering.condenast.io/)* are the _best_.";
        $expected = "Javascript developers* are the best.";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldStripImgTags()
    {
        $string = "![](https://placebear.com/640/480)*Javascript* developers are the _best_.";
        $expected = "Javascript developers are the best.";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldUseTheAltTextOfAnImageIfItIsProvided()
    {
        $string = "![This is the alt-text](https://www.example.com/images/logo.png)";
        $expected = "This is the alt-text";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldStripCodeTags()
    {
        $string = "In `Getting Started` we set up `something` foo.";
        $expected = "In Getting Started we set up something foo.";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldLeaveHashtagsInHeadings()
    {
        $string = "## This #heading contains #hashtags";
        $expected = "This #heading contains #hashtags";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveHeadingsTrailingHashtags()
    {
        $string = "## This #heading contains #hashtags ##";
        $expected = "This #heading contains #hashtags";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveHeadingsHashtags()
    {
        $string = "## This #heading contains #hashtags";
        $expected = "This #heading contains #hashtags";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveEmphasis()
    {
        $string = "I italicized an *I* and it _made_ me *sad*.";
        $expected = "I italicized an I and it made me sad.";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveDoubleEmphasis()
    {
        $string = "**this sentence has __double styling__**";
        $expected = "this sentence has double styling";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveHorizontalRules()
    {
        $string = "Some text on a line\n\n---\n\nA line below";
        $expected = "Some text on a line\n\n\n\n\nA line below";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveHorizontalRulesAndReplaceByAtLeastABreakLine()
    {
        $string = "Some text on a line\n---\nA line below";
        $expected = "Some text on a line\n\n\nA line below";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveHorizontalRulesWithSpaceSeparatedAsterisks()
    {
        $string = "Some text on a line\n\n* * *\n\nA line below";
        $expected = "Some text on a line\n\n\n\n\nA line below";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveBlockquotes()
    {
        $string = ">I am a blockquote";
        $expected = "I am a blockquote";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveBlockquotesWithSpaces()
    {
        $string = "> I am a blockquote";
        $expected = "I am a blockquote";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldRemoveIndentedBlockquotes()
    {
        $string = " > I am a blockquote";
        $expected = "I am a blockquote";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());

        $string = "  > I am a blockquote";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldNotRemoveGreaterThanSigns()
    {
        $tests = [
            ['string' => '100 > 0', 'expected' => '100 > 0'],
            ['string' => '100 >= 0', 'expected' => '100 >= 0'],
            ['string' => '100>0', 'expected' => '100>0'],
            ['string' => '> 100 > 0', 'expected' => '100 > 0'],
            ['string' => '1 < 100', 'expected' => '1 < 100'],
            ['string' => '1 <= 100', 'expected' => '1 <= 100'],
        ];

        foreach ($tests as $test) {
            $this->assertSame(
                $test['expected'],
                (new MarkdownRemover())->process(Text::utf8($test['string']))->getContent()
            );
        }
    }

    public function testShouldStripUnorderedListLeaders()
    {
        $string = "Some text on a line\n\n* A list Item\n* Another list item";
        $expected = "Some text on a line\n\nA list Item\nAnother list item";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldStripOrderedListLeaders()
    {
        $string = "Some text on a line\n\n* A list Item\n* Another list item";
        $expected = "Some text on a line\n\nA list Item\nAnother list item";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldStripOrderedListLeadersKeepingIndentation()
    {
        $string = "Some text on a line\n\n*   A list Item\n    * Another list item";
        $expected = "Some text on a line\n\n  A list Item\n    Another list item";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldStripCodeBlocks()
    {
        $string = <<<CODE
```js
test
```
CODE;

        $expected = <<<CODE

test

CODE;

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }

    public function testShouldHandleParagraphsWithMarkdown()
    {
        $string = "\n## This is a heading ##\n\nThis is a paragraph with [a link](http://www.disney.com/).\n\n### This is another heading\n\nIn `Getting Started` we set up `something` foo.\n\n  * Some list\n  * With items\n    * Even indented";
        $expected = "\nThis is a heading\n\nThis is a paragraph with a link.\n\nThis is another heading\n\nIn Getting Started we set up something foo.\n\n  Some list\n  With items\n    Even indented";

        $this->assertSame($expected, (new MarkdownRemover())->process(Text::utf8($string))->getContent());
    }
}
