
'use client';

import type * as React from 'react';
import { useState } from 'react';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { format } from 'date-fns';

import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Calendar } from "@/components/ui/calendar";
import { CalendarIcon, CheckCircle } from "lucide-react";
import { cn } from "@/lib/utils";
import { useToast } from "@/hooks/use-toast";
import { submitMembershipApplication } from '@/actions/membership-application';

const membershipBenefits = [
  "Active participation in religious and cultural events.",
  "Access to community resources and support networks.",
  "Voting rights in Mandal elections and decision-making processes (as per Mandal rules).",
  "Discounts on paid events and activities, where applicable.",
  "Opportunities to volunteer and contribute to community service.",
  "Regular updates and newsletters about Mandal activities."
];

const formSchema = z.object({
  firstName: z.string().min(1, "First name is required"),
  middleName: z.string().optional(),
  surname: z.string().min(1, "Surname is required"),
  gender: z.enum(["Male", "Female"], { required_error: "Gender is required" }),
  postalAddress: z.string().min(1, "Postal address is required"),
  pinCode: z.string().length(6, "Pin code must be 6 digits").regex(/^\d{6}$/, "Invalid pin code"),
  mobileNo: z.string().length(10, "Mobile number must be 10 digits").regex(/^\d{10}$/, "Invalid mobile number"),
  email: z.string().email("Invalid email address").transform(val => val.toUpperCase()),
  dateOfBirth: z.date({ required_error: "Date of birth is required" }),
  occupation: z.string().min(1, "Occupation is required"),
  qualification: z.string().min(1, "Qualification is required"),
  maritalStatus: z.enum(["Married", "Unmarried"], { required_error: "Marital status is required" }),
  numChildren: z.string().optional().refine(val => !val || /^\d+$/.test(val), {
    message: "Number of children must be a non-negative integer",
  }),
  gotra: z.string().min(1, "Gotra is required"),
  kuladevata: z.string().min(1, "Kuladevata is required"),
  math: z.enum(["Kashi", "Gokarn", "Kavale"], { required_error: "Math is required" }),
  nativePlace: z.string().min(1, "Native place is required"),
  otherGSBInstitutions: z.string().optional(),
  membershipType: z.enum(["Life", "Patron"], { required_error: "Membership type is required" }),
  introducerName: z.string().optional(),
  declaration: z.boolean().refine(val => val === true, {
    message: "You must agree to the declaration",
  }),
}).refine(data => data.maritalStatus === "Unmarried" || (data.maritalStatus === "Married" && data.numChildren !== undefined && data.numChildren !== null && data.numChildren !== ""), {
  message: "Number of children is required if married",
  path: ["numChildren"],
});


export type MembershipFormData = z.infer<typeof formSchema>;

export default function MembershipPage() {
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const form = useForm<MembershipFormData>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      firstName: "",
      middleName: "",
      surname: "",
      postalAddress: "",
      pinCode: "",
      mobileNo: "",
      email: "",
      occupation: "",
      qualification: "",
      numChildren: "", // Keep as string for input, server action will parse if needed
      gotra: "",
      kuladevata: "",
      nativePlace: "",
      otherGSBInstitutions: "",
      introducerName: "",
      declaration: false,
    },
  });

  const onSubmit = async (data: MembershipFormData) => {
    setIsSubmitting(true);
    const dataToSubmit = {
        ...data,
        dateOfBirth: format(data.dateOfBirth, 'dd-MM-yyyy'), // Format date for submission
        numChildren: data.numChildren ? parseInt(data.numChildren, 10) : undefined,
    };

    try {
      const result = await submitMembershipApplication(dataToSubmit);
      if (result.success) {
        toast({
          title: "Application Submitted!",
          description: "Your membership application has been received.",
        });
        form.reset(); // Reset form fields
      } else {
        toast({
          title: "Submission Failed",
          description: result.message || "An error occurred. Please try again.",
          variant: "destructive",
        });
      }
    } catch (error) {
       toast({
        title: "Submission Error",
        description: "An unexpected error occurred. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };
  
  const maritalStatus = form.watch("maritalStatus");

  return (
    <div className="space-y-8">
      <Card>
        <CardHeader>
          <CardTitle className="text-3xl font-bold text-primary">Become a Member</CardTitle>
          <CardDescription>Join the GSB Mandal Thane family by filling out the application form below.</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="prose max-w-none">
            <p>
              Membership in GSB Mandal Thane is open to all Goud Saraswat Brahmin individuals and families residing in Thane and its surrounding areas who wish to connect with their roots, participate in cultural and religious activities, and contribute to the community's growth.
            </p>
            
            <h2 className="text-2xl font-semibold text-primary mt-6 mb-3">Why Become a Member?</h2>
            <p>
              By becoming a member, you gain access to a wide range of benefits and opportunities:
            </p>
            <ul className="space-y-2 my-4">
              {membershipBenefits.map((benefit, index) => (
                <li key={index} className="flex items-start">
                  <CheckCircle className="h-5 w-5 text-accent mr-2 mt-1 flex-shrink-0" />
                  <span>{benefit}</span>
                </li>
              ))}
            </ul>
          </div>

          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8 mt-8">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <FormField
                  control={form.control}
                  name="firstName"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>First Name</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter first name" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="middleName"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Middle Name (Optional)</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter middle name" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="surname"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Surname</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter surname" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <FormField
                control={form.control}
                name="gender"
                render={({ field }) => (
                  <FormItem className="space-y-3">
                    <FormLabel>Gender</FormLabel>
                    <FormControl>
                      <RadioGroup
                        onValueChange={field.onChange}
                        defaultValue={field.value}
                        className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4"
                        disabled={isSubmitting}
                      >
                        <FormItem className="flex items-center space-x-3 space-y-0">
                          <FormControl>
                            <RadioGroupItem value="Male" />
                          </FormControl>
                          <FormLabel className="font-normal">Male</FormLabel>
                        </FormItem>
                        <FormItem className="flex items-center space-x-3 space-y-0">
                          <FormControl>
                            <RadioGroupItem value="Female" />
                          </FormControl>
                          <FormLabel className="font-normal">Female</FormLabel>
                        </FormItem>
                      </RadioGroup>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="postalAddress"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Postal Address</FormLabel>
                    <FormControl>
                      <Textarea placeholder="Enter full postal address" {...field} disabled={isSubmitting} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                  control={form.control}
                  name="pinCode"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Pin Code</FormLabel>
                      <FormControl>
                        <Input type="text" maxLength={6} placeholder="Enter 6-digit pin code" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="mobileNo"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Mobile No.</FormLabel>
                      <FormControl>
                        <Input type="tel" maxLength={10} placeholder="Enter 10-digit mobile number" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                  control={form.control}
                  name="email"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Email (ALL CAPS will be applied)</FormLabel>
                      <FormControl>
                        <Input type="email" placeholder="you@example.com" {...field} onChange={(e) => field.onChange(e.target.value.toUpperCase())} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="dateOfBirth"
                  render={({ field }) => (
                    <FormItem className="flex flex-col">
                      <FormLabel>Date of Birth (dd-mm-yyyy)</FormLabel>
                      <Popover>
                        <PopoverTrigger asChild>
                          <FormControl>
                            <Button
                              variant={"outline"}
                              className={cn(
                                "w-full justify-start text-left font-normal",
                                !field.value && "text-muted-foreground"
                              )}
                              disabled={isSubmitting}
                            >
                              <CalendarIcon className="mr-2 h-4 w-4" />
                              {field.value ? format(field.value, "dd-MM-yyyy") : <span>Pick a date</span>}
                            </Button>
                          </FormControl>
                        </PopoverTrigger>
                        <PopoverContent className="w-auto p-0" align="start">
                          <Calendar
                            mode="single"
                            selected={field.value}
                            onSelect={field.onChange}
                            disabled={(date) =>
                              date > new Date() || date < new Date("1900-01-01")
                            }
                            initialFocus
                            captionLayout="dropdown-buttons"
                            fromYear={1900}
                            toYear={new Date().getFullYear()}
                          />
                        </PopoverContent>
                      </Popover>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                  control={form.control}
                  name="occupation"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Occupation</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter occupation" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="qualification"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Qualification</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter qualification" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <FormField
                  control={form.control}
                  name="maritalStatus"
                  render={({ field }) => (
                    <FormItem className="space-y-3">
                      <FormLabel>Marital Status</FormLabel>
                      <FormControl>
                        <RadioGroup
                          onValueChange={field.onChange}
                          defaultValue={field.value}
                          className="flex space-x-4"
                          disabled={isSubmitting}
                        >
                          <FormItem className="flex items-center space-x-3 space-y-0">
                            <FormControl>
                              <RadioGroupItem value="Married" />
                            </FormControl>
                            <FormLabel className="font-normal">Married</FormLabel>
                          </FormItem>
                          <FormItem className="flex items-center space-x-3 space-y-0">
                            <FormControl>
                              <RadioGroupItem value="Unmarried" />
                            </FormControl>
                            <FormLabel className="font-normal">Unmarried</FormLabel>
                          </FormItem>
                        </RadioGroup>
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                {maritalStatus === 'Married' && (
                  <FormField
                    control={form.control}
                    name="numChildren"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Number of Children (if married)</FormLabel>
                        <FormControl>
                          <Input type="number" min="0" placeholder="Enter number of children" {...field} disabled={isSubmitting} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                )}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <FormField
                  control={form.control}
                  name="gotra"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Gotra</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter Gotra" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="kuladevata"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Kuladevata</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter Kuladevata" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                  control={form.control}
                  name="math"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Math</FormLabel>
                      <Select onValueChange={field.onChange} defaultValue={field.value} disabled={isSubmitting}>
                        <FormControl>
                          <SelectTrigger>
                            <SelectValue placeholder="Select Math" />
                          </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                          <SelectItem value="Kashi">Kashi</SelectItem>
                          <SelectItem value="Gokarn">Gokarn</SelectItem>
                          <SelectItem value="Kavale">Kavale</SelectItem>
                        </SelectContent>
                      </Select>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="nativePlace"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Native Place</FormLabel>
                      <FormControl>
                        <Input placeholder="Enter native place" {...field} disabled={isSubmitting} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <FormField
                control={form.control}
                name="otherGSBInstitutions"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Other GSB institutions you are a member of (if any)</FormLabel>
                    <FormControl>
                      <Textarea placeholder="Mention other GSB institutions" {...field} disabled={isSubmitting} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="membershipType"
                render={({ field }) => (
                  <FormItem className="space-y-3">
                    <FormLabel>Class of Membership Applied For</FormLabel>
                    <FormControl>
                      <RadioGroup
                        onValueChange={field.onChange}
                        defaultValue={field.value}
                        className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4"
                        disabled={isSubmitting}
                      >
                        <FormItem className="flex items-center space-x-3 space-y-0">
                          <FormControl>
                            <RadioGroupItem value="Life" />
                          </FormControl>
                          <FormLabel className="font-normal">Life (₹202/-)</FormLabel>
                        </FormItem>
                        <FormItem className="flex items-center space-x-3 space-y-0">
                          <FormControl>
                            <RadioGroupItem value="Patron" />
                          </FormControl>
                          <FormLabel className="font-normal">Patron (₹502/-)</FormLabel>
                        </FormItem>
                      </RadioGroup>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="introducerName"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Introducer's Name (Optional)</FormLabel>
                    <FormControl>
                      <Input placeholder="Enter introducer's name" {...field} disabled={isSubmitting} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="declaration"
                render={({ field }) => (
                  <FormItem className="flex flex-row items-start space-x-3 space-y-0 rounded-md border p-4 shadow">
                    <FormControl>
                      <Checkbox checked={field.value} onCheckedChange={field.onChange} disabled={isSubmitting} />
                    </FormControl>
                    <div className="space-y-1 leading-none">
                      <FormLabel>Declaration</FormLabel>
                      <FormDescription>
                        I declare that the above information is true to the best of my knowledge & belief. I agree to pay the required membership fee, if my application is accepted. I declare that I am a G.S.B. & have completed 18 years of age. I agree to abide by the Rules & Regulations of the Mandal.
                      </FormDescription>
                      <FormMessage />
                    </div>
                  </FormItem>
                )}
              />

              <CardFooter className="flex-col items-start p-0 pt-6">
                <Button type="submit" size="lg" disabled={isSubmitting}>
                  {isSubmitting ? 'Submitting...' : 'Submit Application'}
                </Button>
                <p className="text-sm text-muted-foreground mt-4">
                  <strong>Note:</strong> The mode of payment for the membership fee (Cheque or Online) will be communicated after your application is provisionally approved.
                  Membership shall be considered as confirmed, only after the payment receipt is issued by the Mandal.
                </p>
              </CardFooter>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  );
}
