import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Save, Palette } from 'lucide-react';

const PRESET_COLORS = [
    '#3B82F6', // Blue
    '#EF4444', // Red
    '#10B981', // Green
    '#F59E0B', // Yellow
    '#8B5CF6', // Purple
    '#F97316', // Orange
    '#06B6D4', // Cyan
    '#84CC16', // Lime
    '#EC4899', // Pink
    '#6B7280', // Gray
];

export default function Create() {
    const [showColorPicker, setShowColorPicker] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        slug: '',
        description: '',
        color: '#3B82F6',
        seo_title: '',
        seo_description: '',
        seo_keywords: '',
        is_active: true,
        sort_order: 0,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.tags.store'));
    };

    const generateSlug = (name: string) => {
        return name
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    };

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const name = e.target.value;
        setData('name', name);
        
        // Auto-generate slug if it's empty or matches the current name's slug
        if (!data.slug || data.slug === generateSlug(data.name)) {
            setData('slug', generateSlug(name));
        }
    };

    return (
        <AdminLayout>
            <Head title="Create Tag" />
            
            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('admin.tags.index')}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Tags
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Tag</h1>
                        <p className="text-muted-foreground">Add a new tag to organize your blog content</p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div className="lg:col-span-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tag Information</CardTitle>
                                    <CardDescription>
                                        Basic information about the tag
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="name">Name *</Label>
                                            <Input
                                                id="name"
                                                type="text"
                                                value={data.name}
                                                onChange={handleNameChange}
                                                placeholder="Enter tag name"
                                                className={errors.name ? 'border-destructive' : ''}
                                            />
                                            {errors.name && (
                                                <p className="text-sm text-destructive">{errors.name}</p>
                                            )}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="slug">Slug</Label>
                                            <Input
                                                id="slug"
                                                type="text"
                                                value={data.slug}
                                                onChange={(e) => setData('slug', e.target.value)}
                                                placeholder="auto-generated"
                                                className={errors.slug ? 'border-destructive' : ''}
                                            />
                                            {errors.slug && (
                                                <p className="text-sm text-destructive">{errors.slug}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Describe what this tag represents..."
                                            rows={3}
                                            className={errors.description ? 'border-destructive' : ''}
                                        />
                                        {errors.description && (
                                            <p className="text-sm text-destructive">{errors.description}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Color</Label>
                                        <div className="flex items-center gap-4">
                                            <div className="flex gap-2">
                                                {PRESET_COLORS.map((color) => (
                                                    <button
                                                        key={color}
                                                        type="button"
                                                        onClick={() => setData('color', color)}
                                                        className={`w-8 h-8 rounded-full border-2 ${
                                                            data.color === color 
                                                                ? 'border-foreground scale-110' 
                                                                : 'border-border hover:scale-105'
                                                        } transition-transform`}
                                                        style={{ backgroundColor: color }}
                                                    />
                                                ))}
                                            </div>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => setShowColorPicker(!showColorPicker)}
                                            >
                                                <Palette className="h-4 w-4 mr-2" />
                                                Custom
                                            </Button>
                                        </div>
                                        {showColorPicker && (
                                            <Input
                                                type="color"
                                                value={data.color}
                                                onChange={(e) => setData('color', e.target.value)}
                                                className="w-20 h-10"
                                            />
                                        )}
                                        {errors.color && (
                                            <p className="text-sm text-destructive">{errors.color}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="sort_order">Sort Order</Label>
                                        <Input
                                            id="sort_order"
                                            type="number"
                                            min="0"
                                            value={data.sort_order}
                                            onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                            className={errors.sort_order ? 'border-destructive' : ''}
                                        />
                                        {errors.sort_order && (
                                            <p className="text-sm text-destructive">{errors.sort_order}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>SEO Settings</CardTitle>
                                    <CardDescription>
                                        Search engine optimization settings for this tag
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="seo_title">SEO Title</Label>
                                        <Input
                                            id="seo_title"
                                            type="text"
                                            value={data.seo_title}
                                            onChange={(e) => setData('seo_title', e.target.value)}
                                            placeholder="Enter SEO title (max 60 characters)"
                                            maxLength={60}
                                            className={errors.seo_title ? 'border-destructive' : ''}
                                        />
                                        <div className="flex justify-between text-xs text-muted-foreground">
                                            <span>{errors.seo_title || 'Recommended: 50-60 characters'}</span>
                                            <span>{data.seo_title.length}/60</span>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="seo_description">SEO Description</Label>
                                        <Textarea
                                            id="seo_description"
                                            value={data.seo_description}
                                            onChange={(e) => setData('seo_description', e.target.value)}
                                            placeholder="Enter SEO description (max 160 characters)"
                                            maxLength={160}
                                            rows={3}
                                            className={errors.seo_description ? 'border-destructive' : ''}
                                        />
                                        <div className="flex justify-between text-xs text-muted-foreground">
                                            <span>{errors.seo_description || 'Recommended: 150-160 characters'}</span>
                                            <span>{data.seo_description.length}/160</span>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="seo_keywords">SEO Keywords</Label>
                                        <Input
                                            id="seo_keywords"
                                            type="text"
                                            value={data.seo_keywords}
                                            onChange={(e) => setData('seo_keywords', e.target.value)}
                                            placeholder="keyword1, keyword2, keyword3"
                                            className={errors.seo_keywords ? 'border-destructive' : ''}
                                        />
                                        {errors.seo_keywords && (
                                            <p className="text-sm text-destructive">{errors.seo_keywords}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <div>
                            <Card>
                                <CardHeader>
                                    <CardTitle>Settings</CardTitle>
                                    <CardDescription>
                                        Tag visibility and status
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div className="space-y-0.5">
                                            <Label htmlFor="is_active">Active Status</Label>
                                            <p className="text-sm text-muted-foreground">
                                                Make this tag available for use
                                            </p>
                                        </div>
                                        <Switch
                                            id="is_active"
                                            checked={data.is_active}
                                            onCheckedChange={(checked) => setData('is_active', checked)}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Preview</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center gap-2 p-3 border rounded-lg">
                                        <div 
                                            className="w-4 h-4 rounded-full border" 
                                            style={{ backgroundColor: data.color }}
                                        />
                                        <span className="font-medium">
                                            {data.name || 'Tag Name'}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>

                            <div className="flex gap-3">
                                <Button type="submit" disabled={processing} className="flex-1">
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Creating...' : 'Create Tag'}
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={route('admin.tags.index')}>
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
